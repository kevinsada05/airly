<?php

use App\Models\ImageUpload;
use App\Models\Zone;
use App\Models\WasteScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use App\Jobs\AnalyzeImageUpload;

Route::get('/', function (Request $request) {
    $uploadsQuery = ImageUpload::query()
        ->with('analysisResult')
        ->latest();

    $scansQuery = WasteScan::query()->latest();

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
        $request->user()?->forceFill(['is_admin' => true])->save();

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
        'name.max' => 'Emri nuk mund të jetë më i gjatë se 255 karaktere.',
        'email.required' => 'Email është i detyrueshëm.',
        'email.email' => 'Email nuk është i vlefshëm.',
        'email.max' => 'Email nuk mund të jetë më i gjatë se 255 karaktere.',
        'email.unique' => 'Ky email ekziston tashmë.',
        'password.required' => 'Fjalëkalimi është i detyrueshëm.',
        'password.min' => 'Fjalëkalimi duhet të ketë të paktën 8 karaktere.',
        'password.confirmed' => 'Konfirmimi i fjalëkalimit nuk përputhet.',
    ]);

    $user = \App\Models\User::query()->create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => $data['password'],
        'is_admin' => true,
    ]);

    Auth::login($user);
    $request->session()->regenerate();

    return redirect()->route('dashboard');
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
    $query = ImageUpload::query()
        ->with('wasteScan')
        ->latest();

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
    $imageUrl = $request->getSchemeAndHttpHost() . '/storage/' . $upload->file_path;

    $upload->load(['wasteScan', 'zones']);

    return view('uploads.show', compact('upload', 'imageUrl'));
})->name('uploads.show');

Route::delete('/uploads/{upload}', function (ImageUpload $upload, Request $request) {
    $viewer = $request->user();
    abort_unless($viewer && $viewer->is_admin, 403);

    try {
        Storage::disk('public')->delete($upload->file_path);
    } catch (Throwable $e) {
        // Continue even if file deletion fails.
    }

    $upload->analysisResult()?->delete();
    $upload->wasteScan()?->delete();
    $upload->zones()->detach();
    $upload->delete();

    return redirect()->route('uploads.index');
})->middleware('auth')->name('uploads.destroy');

Route::get('/zones', function (Request $request) {
    $zonesQuery = Zone::query()->orderByRaw("case current_severity when 'red' then 1 when 'orange' then 2 when 'green' then 3 else 4 end")
        ->orderBy('name');

    $zonesQuery->withCount('imageUploads');

    $zones = $zonesQuery->get();

    return view('zones.index', compact('zones'));
})->name('zones.index');

Route::get('/zones/{zone}', function (Zone $zone, Request $request) {
    $uploadsQuery = $zone->imageUploads()
        ->with('analysisResult')
        ->latest();

    $uploads = $uploadsQuery->get()
        ->map(function (ImageUpload $upload) {
            $upload->image_url = url('/storage/' . $upload->file_path);
            return $upload;
        });

    return view('zones.show', compact('zone', 'uploads'));
})->name('zones.show');

Route::get('/map', function () {
    $zones = Zone::query()
        ->with(['imageUploads.wasteScan'])
        ->get()
        ->map(function (Zone $zone) {
            $hasBurning = $zone->imageUploads->contains(function (ImageUpload $upload) {
                $scan = $upload->wasteScan;
                if (!$scan) {
                    return false;
                }

                $burningText = mb_strtolower(trim((string) $scan->item_type . ' ' . (string) $scan->warnings));
                return (bool) preg_match('/\b(djeg|zjarr|tym|djegie)\b/u', $burningText);
            });

        return [
            'id' => $zone->id,
            'name' => $zone->name,
            'severity' => $zone->current_severity->value ?? $zone->current_severity,
            'polygon' => $zone->polygon,
            'has_burning' => $hasBurning,
        ];
    });

    return view('map.index', [
        'zonesJson' => $zones->toJson(),
    ]);
})->name('map.index');

Route::get('/scanner', function (Request $request) {
    $scanId = $request->query('scan');
    $scan = null;

    if ($scanId) {
        $scan = WasteScan::query()->where('id', $scanId)->first();
    }

    $recentScans = WasteScan::query()->latest()->take(8)->get();
    $imageUrl = $scan ? $request->getSchemeAndHttpHost() . '/storage/' . $scan->file_path : null;

    return view('scanner.index', compact('scan', 'imageUrl', 'recentScans'));
})->name('scanner.index');


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
