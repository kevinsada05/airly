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
use App\Services\WasteScanAnalyzer;

Route::get('/', function (Request $request) {
    $viewer = $request->user();
    $adminId = User::query()->where('is_admin', true)->value('id');

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
    $adminId = User::query()->where('is_admin', true)->value('id');

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
    $adminId = User::query()->where('is_admin', true)->value('id');

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
    $adminId = User::query()->where('is_admin', true)->value('id');

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
    $adminId = User::query()->where('is_admin', true)->value('id');

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
    $adminId = User::query()->where('is_admin', true)->value('id');

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
        $scan->update(WasteScanAnalyzer::analyze($scan));
    } catch (Throwable $e) {
        return redirect()->route('scanner.index')
            ->withErrors(['image' => 'Analiza dështoi. Provoni përsëri.']);
    }

    return redirect()->route('scanner.index', ['scan' => $scan->id]);
})->middleware('auth')->name('scanner.store');

Route::get('/admin/scanner', function () {
    abort_unless(auth()->check() && auth()->user()->is_admin, 403);

    $recentScans = WasteScan::query()->latest()->take(12)->get();

    return view('scanner.admin', compact('recentScans'));
})->middleware('auth')->name('scanner.admin');

Route::post('/scanner/{scan}/reanalyze', function (WasteScan $scan) {
    abort_unless(auth()->check() && auth()->user()->is_admin, 403);

    try {
        $scan->update(WasteScanAnalyzer::analyze($scan));
    } catch (Throwable $e) {
        return redirect()->route('scanner.index', ['scan' => $scan->id])
            ->withErrors(['image' => 'Rianaliza dështoi. Provoni përsëri.']);
    }

    return redirect()->route('scanner.index', ['scan' => $scan->id]);
})->middleware('auth')->name('scanner.reanalyze');

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
