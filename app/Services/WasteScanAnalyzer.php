<?php

namespace App\Services;

use App\Models\WasteScan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class WasteScanAnalyzer
{
    public static function analyze(WasteScan $scan): array
    {
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
- item_type (string, p.sh. mbetje, organike, metal, qelq, plastikë)
- recyclable (boolean | null)
- instructions (string, 6-7 fjali, udhëzime të qarta)
- warnings (string opsionale)

Nëse imazhi NUK duket si mbetje/ndotje (p.sh. logo, dokument, kafshë, peizazh):
- vendos item_type: "e paidentifikueshme"
- vendos recyclable: null
- vendos instructions: "Ky imazh nuk duket si mbetje për riciklim. Ngarko një foto të një objekti/mbetjeje për udhëzime."
- vendos warnings vetëm nëse ka arsye reale.

Udhëzime për `instructions` kur është mbetje e vlefshme:
- Shkruaj 6-7 fjali të plota, jo lista.
- Përshkruaj hapa të sigurt: mbledhje, ndarje, pastrim i lehtë nëse duhet, dhe dorëzim.
- Përmend se djegia është e dëmshme dhe të shmanget.
- Përdor gjuhë të thjeshtë dhe fjali të shkurtra.

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
                'max_output_tokens' => 650,
            ]);

        if (!$response->ok()) {
            throw new RuntimeException('OpenAI API error: ' . $response->status());
        }

        $responseJson = $response->json();
        $analysisText = extract_openai_text($responseJson);
        $analysis = parse_openai_json($analysisText);

        $itemType = mb_strtolower((string) ($analysis['item_type'] ?? ''));
        $isUnknown = $itemType === 'e paidentifikueshme';

        if (!$isUnknown) {
            $analysis['instructions'] = "Identifikoni zonën me mbetje dhe shmangni kontaktin e panevojshëm. Mblidhni mbetjet e dukshme në mënyrë të sigurt dhe ndani materialet nëse është e mundur. Hiqni papastërtitë e mëdha dhe mbajini të mbyllura në qese ose enë të veçanta. Mos i digjni mbeturinat, sepse krijojnë ndotje të ajrit dhe rrezik për shëndetin. Dorëzojini në pikat lokale të grumbullimit ose shërbimin komunal të pastrimit. Nëse është zonë problematike, dokumentojeni dhe njoftoni autoritetet lokale për ndërhyrje.";
        }

        return [
            'item_type' => $analysis['item_type'] ?? null,
            'recyclable' => isset($analysis['recyclable']) ? (bool) $analysis['recyclable'] : null,
            'instructions' => $analysis['instructions'] ?? null,
            'warnings' => $analysis['warnings'] ?? null,
            'raw_output' => $responseJson,
            'model_name' => $responseJson['model'] ?? $model,
        ];
    }
}
