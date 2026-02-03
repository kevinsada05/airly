<?php

namespace App\Jobs;

use App\Enums\ImageUploadStatus;
use App\Enums\Severity;
use App\Models\AnalysisResult;
use App\Models\ImageUpload;
use App\Models\Zone;
use App\Models\WasteScan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AnalyzeImageUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $imageUploadId)
    {
    }

    public function handle(): void
    {
        $upload = ImageUpload::query()->find($this->imageUploadId);

        if (!$upload) {
            return;
        }

        $upload->update(['status' => ImageUploadStatus::Processing]);

        try {
            $disk = Storage::disk('public');
            if (!$disk->exists($upload->file_path)) {
                throw new \RuntimeException('Image file not found.');
            }

            $imageData = $disk->get($upload->file_path);
            $mime = $disk->mimeType($upload->file_path) ?? 'image/jpeg';
            $imageUrl = 'data:' . $mime . ';base64,' . base64_encode($imageData);

            $apiKey = config('services.openai.key');
            $model = config('services.openai.model', 'gpt-4.1-mini');

            if (!$apiKey) {
                throw new \RuntimeException('OpenAI API key is missing.');
            }

            $prompt = <<<PROMPT
Return a JSON object with:
- pollution_detected (boolean)
- severity (green or red)
- confidence (number 0-1)
Use severity=red if pollution_detected=true, otherwise green.
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
                    'max_output_tokens' => 200,
                ]);

            if (!$response->ok()) {
                throw new \RuntimeException('OpenAI API error: ' . $response->status());
            }

            $responseJson = $response->json();
            $analysisText = $this->extractText($responseJson);

            if (!$analysisText) {
                throw new \RuntimeException('OpenAI response missing text.');
            }

            $analysis = $this->parseJson($analysisText);

            $polluted = (bool)($analysis['pollution_detected'] ?? false);
            $confidence = isset($analysis['confidence']) ? (float) $analysis['confidence'] : null;
            if ($confidence !== null) {
                $confidence = max(0, min(1, $confidence));
            }

            if ($polluted) {
                if ($confidence !== null && $confidence >= 0.7) {
                    $severity = Severity::Red;
                } elseif ($confidence !== null && $confidence >= 0.4) {
                    $severity = Severity::Orange;
                } else {
                    $severity = Severity::Orange;
                }
            } else {
                $severity = Severity::Green;
            }

            AnalysisResult::query()->updateOrCreate(
                ['image_upload_id' => $upload->id],
                [
                    'pollution_detected' => $polluted,
                    'severity' => $severity,
                    'confidence' => $confidence,
                    'raw_output' => $responseJson,
                    'model_name' => $responseJson['model'] ?? $model,
                    'processed_at' => now(),
                ]
            );

            $upload->update([
                'status' => ImageUploadStatus::Processed,
                'analysis_version' => $responseJson['model'] ?? $model,
            ]);

            $zones = Zone::query()->get();
            $matchedZone = false;
            foreach ($zones as $zone) {
                if ($zone->containsPoint($upload->lat, $upload->lng)) {
                    $zone->imageUploads()->syncWithoutDetaching([$upload->id]);
                    $zone->recalculateSeverity();
                    $matchedZone = true;
                }
            }

            if (!$matchedZone) {
                $zone = $this->createAutoZoneForUpload($upload->lat, $upload->lng);
                $zone->imageUploads()->syncWithoutDetaching([$upload->id]);
                $zone->recalculateSeverity();
            }

            $this->analyzeRecycling($upload, $imageUrl);
        } catch (\Throwable $e) {
            $upload->update(['status' => ImageUploadStatus::Failed]);
            throw $e;
        }
    }

    private function extractText(array $response): ?string
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

    private function parseJson(string $text): array
    {
        $clean = trim($text);
        $clean = preg_replace('/^```json\\s*/', '', $clean);
        $clean = preg_replace('/```$/', '', $clean);

        $decoded = json_decode($clean, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Unable to parse JSON from OpenAI response.');
        }

        return $decoded;
    }

    private function analyzeRecycling(ImageUpload $upload, string $imageUrl): void
    {
        try {
            $apiKey = config('services.openai.key');
            $model = config('services.openai.model', 'gpt-4.1-mini');

            if (!$apiKey) {
                return;
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
                return;
            }

            $responseJson = $response->json();
            $analysisText = $this->extractText($responseJson);

            if (!$analysisText) {
                return;
            }

            $analysis = $this->parseJson($analysisText);

            WasteScan::query()->updateOrCreate(
                ['image_upload_id' => $upload->id],
                [
                    'user_id' => $upload->user_id,
                    'file_path' => $upload->file_path,
                    'item_type' => $analysis['item_type'] ?? null,
                    'recyclable' => isset($analysis['recyclable']) ? (bool) $analysis['recyclable'] : null,
                    'instructions' => $analysis['instructions'] ?? null,
                    'warnings' => $analysis['warnings'] ?? null,
                    'raw_output' => $responseJson,
                    'model_name' => $responseJson['model'] ?? $model,
                ]
            );
        } catch (\Throwable $e) {
            // Keep main analysis unaffected.
        }
    }

    private function createAutoZoneForUpload(float $lat, float $lng): Zone
    {
        $baseName = $this->reverseGeocodeName($lat, $lng);
        $name = $baseName ? "Zona – {$baseName}" : 'Zona e re';
        $name = $this->uniqueZoneName($name);

        $delta = 0.01;
        $polygon = $this->rect(
            $lat + $delta,
            $lng - $delta,
            $lat - $delta,
            $lng + $delta
        );

        return Zone::query()->create([
            'name' => $name,
            'polygon' => $polygon,
            'current_severity' => Severity::Green,
        ]);
    }

    private function reverseGeocodeName(float $lat, float $lng): ?string
    {
        $baseUrl = config('services.nominatim.url', 'https://nominatim.openstreetmap.org');
        $email = config('services.nominatim.email');
        $userAgent = $email ? "Airly/1.0 (contact: {$email})" : 'Airly/1.0';

        try {
            $response = Http::timeout(8)
                ->retry(1, 250)
                ->withHeaders([
                    'User-Agent' => $userAgent,
                ])
                ->get(rtrim($baseUrl, '/') . '/reverse', [
                    'format' => 'jsonv2',
                    'lat' => $lat,
                    'lon' => $lng,
                    'zoom' => 14,
                    'addressdetails' => 1,
                    'email' => $email,
                ]);

            if (!$response->ok()) {
                return null;
            }

            $data = $response->json();
            $address = $data['address'] ?? [];

            $name = $address['city']
                ?? $address['town']
                ?? $address['village']
                ?? $address['municipality']
                ?? $address['state_district']
                ?? $address['suburb']
                ?? $address['neighbourhood']
                ?? $address['quarter']
                ?? null;

            if (!$name && isset($data['display_name'])) {
                $parts = explode(',', (string) $data['display_name']);
                $name = trim($parts[0] ?? '');
            }

            $name = trim((string) $name);
            if ($name === '') {
                return null;
            }

            return substr($name, 0, 60);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function uniqueZoneName(string $base): string
    {
        $name = $base;
        $suffix = 2;

        while (Zone::query()->where('name', $name)->exists()) {
            $name = "{$base} #{$suffix}";
            $suffix++;
        }

        return $name;
    }

    private function rect(float $north, float $west, float $south, float $east): array
    {
        return [
            'type' => 'Polygon',
            'coordinates' => [
                [
                    [$west, $north],
                    [$east, $north],
                    [$east, $south],
                    [$west, $south],
                    [$west, $north],
                ],
            ],
        ];
    }
}
