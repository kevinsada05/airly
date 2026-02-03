<?php

use App\Models\ImageUpload;
use App\Models\User;
use App\Models\Zone;
use App\Models\WasteScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use App\Jobs\AnalyzeImageUpload;

Route::get('/', function () {
    return view('home');
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

Route::get('/register', function () {
    return view('auth.register');
})->middleware('guest')->name('register');

Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ], [
        'name.required' => 'Emri është i detyrueshëm.',
        'email.required' => 'Email është i detyrueshëm.',
        'email.email' => 'Email nuk është i vlefshëm.',
        'email.unique' => 'Ky email është përdorur tashmë.',
        'password.required' => 'Fjalëkalimi është i detyrueshëm.',
        'password.min' => 'Fjalëkalimi duhet të ketë të paktën 8 karaktere.',
        'password.confirmed' => 'Konfirmimi i fjalëkalimit nuk përputhet.',
    ]);

    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
    ]);

    Auth::login($user);

    return redirect('/dashboard');
})->middleware('guest')->name('register.submit');

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
    $uploads = ImageUpload::query()
        ->where('user_id', $request->user()->id)
        ->with('wasteScan')
        ->latest()
        ->paginate(12);

    return view('uploads.index', compact('uploads'));
})->middleware('auth')->name('uploads.index');

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
    abort_unless($upload->user_id === $request->user()->id, 403);

    $imageUrl = $request->getSchemeAndHttpHost() . '/storage/' . $upload->file_path;

    $upload->load(['wasteScan', 'zones']);

    return view('uploads.show', compact('upload', 'imageUrl'));
})->middleware('auth')->name('uploads.show');

Route::get('/zones', function () {
    $zones = Zone::query()
        ->withCount('imageUploads')
        ->orderBy('name')
        ->get();

    return view('zones.index', compact('zones'));
})->middleware('auth')->name('zones.index');

Route::get('/zones/{zone}', function (Zone $zone) {
    $zone->load(['imageUploads.analysisResult']);

    $uploads = $zone->imageUploads()
        ->latest()
        ->get()
        ->map(function (ImageUpload $upload) {
            $upload->image_url = url('/storage/' . $upload->file_path);
            return $upload;
        });

    return view('zones.show', compact('zone', 'uploads'));
})->middleware('auth')->name('zones.show');

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
})->middleware('auth')->name('map.index');

Route::get('/scanner', function (Request $request) {
    $scanId = $request->query('scan');
    $scan = null;

    if ($scanId) {
        $scan = WasteScan::query()
            ->where('id', $scanId)
            ->where('user_id', $request->user()->id)
            ->first();
    }

    $imageUrl = $scan ? $request->getSchemeAndHttpHost() . '/storage/' . $scan->file_path : null;

    return view('scanner.index', compact('scan', 'imageUrl'));
})->middleware('auth')->name('scanner.index');

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
Kthe një JSON me fushat:
- item_type (string, p.sh. plastikë, qelq, metal, organike)
- recyclable (boolean)
- instructions (string, udhëzime të qarta riciklimi)
- warnings (string opsionale)
Përgjigju vetëm me JSON.
PROMPT;

        $response = Http::timeout(60)
            ->retry(2, 500)
            ->withToken($apiKey)
            ->post('https://api.openai.com/v1/responses', [
                'model' => $model,
                'input' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'input_text', 'text' => $prompt],
                            ['type' => 'input_image', 'image_url' => $imageUrl],
                        ],
                    ],
                ],
                'temperature' => 0,
                'max_output_tokens' => 250,
            ]);

        if (!$response->ok()) {
            throw new RuntimeException('OpenAI API error: ' . $response->status());
        }

        $responseJson = $response->json();
        $analysisText = extract_openai_text($responseJson);
        $analysis = parse_openai_json($analysisText);

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
