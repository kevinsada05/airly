<?php

use App\Models\ImageUpload;
use App\Models\User;
use App\Models\Zone;
use App\Models\WasteScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use App\Jobs\AnalyzeImageUpload;

if (!function_exists('admin_user_id')) {
    function admin_user_id(): ?int
    {
        static $adminId = null;
        if ($adminId !== null) {
            return $adminId;
        }

        $adminId = User::query()->where('is_admin', true)->value('id');

        return $adminId;
    }
}

Route::get('/', function (Request $request) {
    $viewer = $request->user();
    $adminId = admin_user_id();

    $visibleIds = [];
    if ($adminId) {
        $visibleIds[] = $adminId;
    }
    if ($viewer && !$viewer->is_admin) {
        $visibleIds[] = $viewer->id;
    }

    $uploadsQuery = ImageUpload::query()
        ->with('analysisResult')
        ->latest();

    if (!$viewer || !$viewer->is_admin) {
        if ($visibleIds) {
            $uploadsQuery->whereIn('user_id', $visibleIds);
        } else {
            $uploadsQuery->whereRaw('1=0');
        }
    }

    $scansQuery = WasteScan::query()->latest();
    if (!$viewer || !$viewer->is_admin) {
        if ($visibleIds) {
            $scansQuery->whereIn('user_id', $visibleIds);
        } else {
            $scansQuery->whereRaw('1=0');
        }
    }

    $recentUploads = $uploadsQuery->take(6)->get();
    $recentScans = $scansQuery->take(6)->get();
    $zones = Zone::query()->orderBy('name')->take(6)->get();

    return view('home', compact('recentUploads', 'recentScans', 'zones'));
})->name('home');

Route::get('/login', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ], [
        'email.required' => 'Email është i detyrueshëm.',
        'email.email' => 'Email nuk është i vlefshëm.',
        'password.required' => 'Fjalëkalimi është i detyrueshëm.',
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    return back()->withErrors([
        'email' => 'Email ose fjalëkalim i pasaktë.',
    ])->onlyInput('email');
})->middleware('guest')->name('login.submit');


Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');

Route::get('/dashboard', function () {
    $userId = auth()->id();

    $uploadBase = ImageUpload::query()->where('user_id', $userId);

    $totalUploads = (clone $uploadBase)->count();
    $pendingCount = (clone $uploadBase)->where('status', 'pending')->count();
    $processingCount = (clone $uploadBase)->where('status', 'processing')->count();
    $processedCount = (clone $uploadBase)->where('status', 'processed')->count();
    $failedCount = (clone $uploadBase)->where('status', 'failed')->count();

    $wasteScanCount = WasteScan::query()
        ->where('user_id', $userId)
        ->count();

    $recentUploads = (clone $uploadBase)
        ->with(['analysisResult', 'wasteScan'])
        ->latest()
        ->take(5)
        ->get();

    $zonesCount = Zone::query()->count();
    $zonesRedCount = Zone::query()->where('current_severity', 'red')->count();
    $zonesGreenCount = Zone::query()->where('current_severity', 'green')->count();

    return view('dashboard.index', compact(
        'totalUploads',
        'pendingCount',
        'processingCount',
        'processedCount',
        'failedCount',
        'wasteScanCount',
        'recentUploads',
        'zonesCount',
        'zonesRedCount',
        'zonesGreenCount'
    ));
})->middleware('auth')->name('dashboard');

Route::get('/uploads', function (Request $request) {
    $viewer = $request->user();
    $adminId = admin_user_id();

    $query = ImageUpload::query()
        ->with('wasteScan')
        ->latest();

    if (!$viewer || !$viewer->is_admin) {
        $visibleIds = [];
        if ($adminId) {
            $visibleIds[] = $adminId;
        }
        if ($viewer) {
            $visibleIds[] = $viewer->id;
        }

        if ($visibleIds) {
            $query->whereIn('user_id', $visibleIds);
        } else {
            $query->whereRaw('1=0');
        }
    }

    $uploads = $query->paginate(12);

    return view('uploads.index', compact('uploads'));
})->name('uploads.index');

Route::get('/uploads/create', function () {
    return view('uploads.create');
})->middleware('auth')->name('uploads.create');

Route::post('/uploads', function (Request $request) {
    $data = $request->validate([
        'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:8192'],
        'lat' => ['required', 'numeric', 'between:-90,90'],
        'lng' => ['required', 'numeric', 'between:-180,180'],
        'note' => ['nullable', 'string', 'max:1000'],
    ], [
        'image.required' => 'Imazhi është i detyrueshëm.',
        'image.image' => 'Skedari duhet të jetë imazh.',
        'image.mimes' => 'Lejohen vetëm JPG/PNG.',
        'image.max' => 'Madhësia maksimale është 8MB.',
        'lat.required' => 'Gjerësia gjeografike është e detyrueshme.',
        'lat.numeric' => 'Gjerësia gjeografike duhet të jetë numër.',
        'lat.between' => 'Gjerësia gjeografike duhet të jetë mes -90 dhe 90.',
        'lng.required' => 'Gjatësia gjeografike është e detyrueshme.',
        'lng.numeric' => 'Gjatësia gjeografike duhet të jetë numër.',
        'lng.between' => 'Gjatësia gjeografike duhet të jetë mes -180 dhe 180.',
        'note.max' => 'Shënimi nuk mund të jetë më i gjatë se 1000 karaktere.',
    ]);

    $path = $request->file('image')->store('uploads', 'public');

    $upload = ImageUpload::create([
        'user_id' => $request->user()->id,
        'lat' => $data['lat'],
        'lng' => $data['lng'],
        'file_path' => $path,
        'source' => 'user',
        'status' => 'pending',
        'note' => $data['note'] ?? null,
    ]);

    AnalyzeImageUpload::dispatch($upload->id);

    return redirect()->route('uploads.show', $upload);
})->middleware('auth')->name('uploads.store');

Route::get('/uploads/{upload}', function (ImageUpload $upload, Request $request) {
    $viewer = $request->user();
    $adminId = admin_user_id();

    if (!$viewer || !$viewer->is_admin) {
        $visibleIds = [];
        if ($adminId) {
            $visibleIds[] = $adminId;
        }
        if ($viewer) {
            $visibleIds[] = $viewer->id;
        }

        abort_unless($visibleIds && in_array($upload->user_id, $visibleIds, true), 403);
    }

    $imageUrl = $request->getSchemeAndHttpHost() . '/storage/' . $upload->file_path;

    $upload->load(['wasteScan', 'zones']);

    return view('uploads.show', compact('upload', 'imageUrl'));
})->name('uploads.show');

Route::get('/zones', function (Request $request) {
    $viewer = $request->user();
    $adminId = admin_user_id();

    $zonesQuery = Zone::query()->orderBy('name');

    if (!$viewer || !$viewer->is_admin) {
        $visibleIds = [];
        if ($adminId) {
            $visibleIds[] = $adminId;
        }
        if ($viewer) {
            $visibleIds[] = $viewer->id;
        }

        if ($visibleIds) {
            $zonesQuery->withCount([
                'imageUploads as image_uploads_count' => function ($query) use ($visibleIds) {
                    $query->whereIn('image_uploads.user_id', $visibleIds);
                },
            ]);
        } else {
            $zonesQuery->withCount([
                'imageUploads as image_uploads_count' => function ($query) {
                    $query->whereRaw('1=0');
                },
            ]);
        }
    } else {
        $zonesQuery->withCount('imageUploads');
    }

    $zones = $zonesQuery->get();

    return view('zones.index', compact('zones'));
})->name('zones.index');

Route::get('/zones/{zone}', function (Zone $zone, Request $request) {
    $viewer = $request->user();
    $adminId = admin_user_id();

    $uploadsQuery = $zone->imageUploads()
        ->with('analysisResult')
        ->latest();

    if (!$viewer || !$viewer->is_admin) {
        $visibleIds = [];
        if ($adminId) {
            $visibleIds[] = $adminId;
        }
        if ($viewer) {
            $visibleIds[] = $viewer->id;
        }

        if ($visibleIds) {
            $uploadsQuery->whereIn('image_uploads.user_id', $visibleIds);
        } else {
            $uploadsQuery->whereRaw('1=0');
        }
    }

    $uploads = $uploadsQuery->get()
        ->map(function (ImageUpload $upload) {
            $upload->image_url = url('/storage/' . $upload->file_path);
            return $upload;
        });

    return view('zones.show', compact('zone', 'uploads'));
})->name('zones.show');

Route::get('/map', function () {
    $zones = Zone::query()->get()->map(function (Zone $zone) {
        return [
            'id' => $zone->id,
            'name' => $zone->name,
            'severity' => $zone->current_severity->value ?? $zone->current_severity,
            'polygon' => $zone->polygon,
        ];
    });

    return view('map.index', [
        'zonesJson' => $zones->toJson(),
    ]);
})->name('map.index');

Route::get('/scanner', function (Request $request) {
    $viewer = $request->user();
    $adminId = admin_user_id();

    $scanId = $request->query('scan');
    $scan = null;

    if ($scanId) {
        $scanQuery = WasteScan::query()->where('id', $scanId);

        if (!$viewer || !$viewer->is_admin) {
            $visibleIds = [];
            if ($adminId) {
                $visibleIds[] = $adminId;
            }
            if ($viewer) {
                $visibleIds[] = $viewer->id;
            }

            if ($visibleIds) {
                $scanQuery->whereIn('user_id', $visibleIds);
            } else {
                $scanQuery->whereRaw('1=0');
            }
        }

        $scan = $scanQuery->first();
    }

    $recentScansQuery = WasteScan::query()->latest();
    if (!$viewer || !$viewer->is_admin) {
        $visibleIds = [];
        if ($adminId) {
            $visibleIds[] = $adminId;
        }
        if ($viewer) {
            $visibleIds[] = $viewer->id;
        }

        if ($visibleIds) {
            $recentScansQuery->whereIn('user_id', $visibleIds);
        } else {
            $recentScansQuery->whereRaw('1=0');
        }
    }

    $recentScans = $recentScansQuery->take(8)->get();
    $imageUrl = $scan ? $request->getSchemeAndHttpHost() . '/storage/' . $scan->file_path : null;

    return view('scanner.index', compact('scan', 'imageUrl', 'recentScans'));
})->name('scanner.index');

Route::post('/scanner', function (Request $request) {
    $data = $request->validate([
        'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:8192'],
    ], [
        'image.required' => 'Imazhi është i detyrueshëm.',
        'image.image' => 'Skedari duhet të jetë imazh.',
        'image.mimes' => 'Lejohen vetëm JPG/PNG.',
        'image.max' => 'Madhësia maksimale është 8MB.',
    ]);

    $path = $request->file('image')->store('scans', 'public');

    $scan = WasteScan::create([
        'user_id' => $request->user()->id,
        'file_path' => $path,
    ]);

    try {
        $disk = Storage::disk('public');
        $imageData = $disk->get($scan->file_path);
        $mime = $disk->mimeType($scan->file_path) ?? 'image/jpeg';
        $imageUrl = 'data:' . $mime . ';base64,' . base64_encode($imageData);

        $apiKey = config('services.openai.key');
        $model = config('services.openai.model', 'gpt-4.1-mini');

        if (!$apiKey) {
            throw new RuntimeException('OpenAI API key missing.');
        }

        $prompt = <<<PROMPT
Kthe vetëm JSON me fushat:
- item_type (string, p.sh. plastikë, qelq, metal, organike)
- recyclable (boolean | null)
- instructions (string, udhëzime të qarta riciklimi, 6-7 fjali)
- warnings (string opsionale)

Nëse imazhi NUK duket si mbetje/objekt riciklimi (p.sh. logo, dokument, kafshë, peizazh):
- vendos item_type: "e paidentifikueshme"
- vendos recyclable: null
- vendos instructions: "Ky imazh nuk duket si mbetje për riciklim. Ngarko një foto të një objekti/mbetjeje për udhëzime."
- vendos warnings vetëm nëse ka arsye reale.

Udhëzime për `instructions` kur është mbetje e vlefshme:
- Shkruaj 6-7 fjali të plota, jo lista.
- Përfshi 3-4 hapa konkretë (p.sh. pastrim, ndarje kapak/etiketë, tharje).
- Shto ku ta dërgojnë (kosh riciklimi, pikë grumbullimi, mbetje të përziera nëse s’riciklohet).
- Përmend nëse ka variante lokale (p.sh. nëse qelqi grumbullohet veç).
- Përdor gjuhë të thjeshtë dhe fjali të shkurtra.

Përgjigju vetëm me JSON.
PROMPT;

        $makeRequest = function (string $promptText) use ($model, $apiKey, $imageUrl) {
            return Http::timeout(60)
                ->retry(2, 500)
                ->withToken($apiKey)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $model,
                    'input' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'input_text', 'text' => $promptText],
                                ['type' => 'input_image', 'image_url' => $imageUrl],
                            ],
                        ],
                    ],
                    'temperature' => 0,
                    'max_output_tokens' => 650,
                ]);
        };

        $response = $makeRequest($prompt);

        if (!$response->ok()) {
            throw new RuntimeException('OpenAI API error: ' . $response->status());
        }

        $responseJson = $response->json();
        $analysisText = extract_openai_text($responseJson);
        $analysis = parse_openai_json($analysisText);

        $instructions = (string) ($analysis['instructions'] ?? '');
        $itemType = mb_strtolower((string) ($analysis['item_type'] ?? ''));
        $recyclable = $analysis['recyclable'] ?? null;

        $sentenceCount = preg_match_all('/[.!?]+/', $instructions);
        $needsRetry = $itemType !== 'e paidentifikueshme'
            && $recyclable !== null
            && ($sentenceCount < 6 || mb_strlen($instructions) < 240);

        if ($needsRetry) {
            $retryPrompt = $prompt . "\n\nKërkesë shtesë: përgjigju me 6-7 fjali të plota në `instructions`, minimumi 240 karaktere. Mos përdor lista ose pika.";
            $retryResponse = $makeRequest($retryPrompt);

            if ($retryResponse->ok()) {
                $retryJson = $retryResponse->json();
                $retryText = extract_openai_text($retryJson);
                $analysis = parse_openai_json($retryText);
                $responseJson = $retryJson;
            }
        }

        $instructions = (string) ($analysis['instructions'] ?? '');
        $itemType = mb_strtolower((string) ($analysis['item_type'] ?? ''));
        $recyclable = $analysis['recyclable'] ?? null;
        $sentenceCount = preg_match_all('/[.!?]+/', $instructions);
        $isUnknown = $itemType === 'e paidentifikueshme';
        $needsFallback = !$isUnknown && ($sentenceCount < 6 || mb_strlen($instructions) < 240);

        if ($needsFallback) {
            $typeLabel = $itemType ?: 'mbetje';
            $disposal = $recyclable ? 'koshin e riciklimit ose pikën e grumbullimit' : 'mbetjet e përziera';
            $extra = $recyclable
                ? 'Nëse komuna juaj ka rregulla të veçanta, ndiqni udhëzimet lokale për këtë material.'
                : 'Nëse materiali ka përbërje të përziera, kërkoni pikë grumbullimi të specializuar.';

            $templates = [
                'organike' => "Mblidhni mbeturinat organike si mbetje ushqimore ose të kopshtit dhe largoni çdo pjesë jo organike. Nëse ka lëngje ose papastërti, hiqini lehtë që të mos krijoni aromë të fortë. Vendosini në enë të posaçme për organike ose në një komposter të mbyllur. Përzieni herë pas here për ajrosje nëse kompostoni në shtëpi. Në mungesë kompostimi, dërgojini në {$disposal} sipas udhëzimeve lokale. {$extra}",
                'plastikë' => "Identifikoni llojin e plastikës dhe hiqni mbetjet e ushqimit ose papastërtitë. Shpëlajeni shpejt dhe lëreni të thahet që të mos ndotë materialet e tjera. Hiqni kapakët ose etiketat nëse janë materiale të ndryshme. Shtrydhni shishet për të kursyer hapësirë dhe ruajini të ndara. Dërgojini në {$disposal} sipas kategorisë së plastikës. {$extra}",
                'qelq' => "Mblidhni qelqin veçmas dhe hiqni mbetjet e ushqimit ose lëngjet. Shpëlajeni lehtë dhe lëreni të thahet para dorëzimit. Hiqni kapakët metalikë ose plastikë dhe ndajini veç. Mos përzieni qelqin me qeramikë ose pasqyra sepse nuk riciklohen njësoj. Dërgojeni në {$disposal} ose në kontejnerët e veçantë për qelq. {$extra}",
                'metal' => "Mblidhni metalet veçmas dhe hiqni papastërtitë ose mbetjet e ushqimit. Shpëlajini lehtë dhe lërini të thahen para dorëzimit. Nda kapakët ose pjesët e tjera jo metalike që mund të hiqen. Nëse ka kanaçe, shtypini lehtë për të kursyer hapësirë. Dërgojini në {$disposal} ose në qendra riciklimi metalesh. {$extra}",
                'letra' => "Mblidhni letrën dhe kartonin të thatë dhe hiqni elementët plastikë ose metalikë. Mos e përzieni me letër të lagur ose të ndotur me vaj. Paloseni ose shtrydheni për të kursyer hapësirë. Nda kartonin e trashë nga letra e hollë nëse ka udhëzime të veçanta. Dërgojeni në {$disposal} në ditët e grumbullimit ose në pika të dedikuara. {$extra}",
            ];

            $analysis['instructions'] = $templates[$itemType] ?? "Identifiko materialin si {$typeLabel} dhe verifiko nëse është i pastër. Hiq mbetjet e ushqimit ose papastërtitë dhe shpëlaje lehtë nëse është e nevojshme. Nda komponentët shtesë si kapakë, etiketë ose pjesë metalike që mund të ndahen. Lëre të thahet që të mos kontaminojë materialet e tjera. Dërgoje në {$disposal}, duke e vendosur të veçuar sipas llojit të materialit. {$extra}";
        }

        $scan->update([
            'item_type' => $analysis['item_type'] ?? null,
            'recyclable' => isset($analysis['recyclable']) ? (bool) $analysis['recyclable'] : null,
            'instructions' => $analysis['instructions'] ?? null,
            'warnings' => $analysis['warnings'] ?? null,
            'raw_output' => $responseJson,
            'model_name' => $responseJson['model'] ?? $model,
        ]);
    } catch (Throwable $e) {
        return redirect()->route('scanner.index')
            ->withErrors(['image' => 'Analiza dështoi. Provoni përsëri.']);
    }

    return redirect()->route('scanner.index', ['scan' => $scan->id]);
})->middleware('auth')->name('scanner.store');

if (!function_exists('extract_openai_text')) {
    function extract_openai_text(array $response): ?string
    {
        $output = $response['output'] ?? [];
        foreach ($output as $item) {
            $content = $item['content'] ?? [];
            foreach ($content as $part) {
                if (isset($part['text']) && is_string($part['text'])) {
                    return $part['text'];
                }
            }
        }

        return $response['output_text'] ?? null;
    }
}

if (!function_exists('parse_openai_json')) {
    function parse_openai_json(?string $text): array
    {
        if (!$text) {
            throw new RuntimeException('Missing OpenAI response text.');
        }

        $clean = trim($text);
        $clean = preg_replace('/^```json\\s*/', '', $clean);
        $clean = preg_replace('/```$/', '', $clean);

        $decoded = json_decode($clean, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Unable to parse JSON from OpenAI response.');
        }

        return $decoded;
    }
}
