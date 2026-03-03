<?php

namespace App\Services;

use App\Models\WasteScan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class WasteScanAnalyzer
{
    public static function analyzeFile(string $filePath): array
    {
        $disk = Storage::disk('public');
        $imageData = $disk->get($filePath);
        $mime = $disk->mimeType($filePath) ?? 'image/jpeg';
        $imageUrl = 'data:' . $mime . ';base64,' . base64_encode($imageData);

        return self::analyzeImageUrl($imageUrl);
    }

    public static function analyze(WasteScan $scan): array
    {
        return self::analyzeFile($scan->file_path);
    }

    private static function analyzeImageUrl(string $imageUrl): array
    {
        $apiKey = config('services.openai.key');
        $model = config('services.openai.model', 'gpt-4.1-mini');

        if (!$apiKey) {
            throw new RuntimeException('OpenAI API key missing.');
        }

        $prompt = <<<PROMPT
Kthe vetëm JSON me fushat:
- item_type (string, p.sh. grumbullim mbetjesh, ndotje e shpërndarë, djegie mbetjesh)
- severity (string: green | orange | purple)
- instructions (string, 6-7 fjali, udhëzime të qarta)
- warnings (string opsionale)

Rregulla:
- Mos përdor "e paidentifikueshme". Gjithmonë zgjidh një kategori relevante.
- Nëse ka shenja të djegies (tym/zjarr/djegie), vendos item_type: "djegie mbetjesh" dhe severity: "purple".
- Nëse ka grumbullim mbetjesh pa zjarr, vendos severity: "orange".
- Nëse ndotja është e lehtë ose e shpërndarë, vendos severity: "orange".
- Nëse duken mjete elektronike ose bateri, shto paralajmërim të qartë për rrezikun e tyre.

Udhëzime për `instructions`:
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
        $analysisText = self::extractText($responseJson);
        $analysis = self::parseJson($analysisText);

        $itemType = mb_strtolower((string) ($analysis['item_type'] ?? ''));
        $rawWarnings = trim((string) ($analysis['warnings'] ?? ''));
        $combinedText = mb_strtolower(trim(
            (string) ($analysis['item_type'] ?? '') . ' ' .
            (string) ($analysis['warnings'] ?? '') . ' ' .
            (string) ($analysis['instructions'] ?? '')
        ));
        $mentionsBurning = (bool) preg_match('/\\b(djeg|zjarr|tym|djegie)\\b/u', $combinedText);
        $mentionsElectronics = (bool) preg_match('/\\b(elektronik|elektronike|elektronikeve|bateri|akumulator|telefon|laptop|kompjut|tv|televiz|printer|kabllo|kabel|e-waste|ewaste)\\b/u', $combinedText);

        if ($mentionsBurning) {
            $itemType = 'djegie mbetjesh';
        }

        $normalizedType = $itemType;
        if (mb_stripos($normalizedType, 'djeg') !== false) {
            $normalizedType = 'djegie mbetjesh';
        } elseif (mb_stripos($normalizedType, 'grumbull') !== false) {
            $normalizedType = 'grumbullim mbetjesh';
        } elseif (mb_stripos($normalizedType, 'shpërndar') !== false || mb_stripos($normalizedType, 'shperndar') !== false) {
            $normalizedType = 'ndotje e shpërndarë';
        } else {
            $normalizedType = 'ndotje e mundshme';
        }

        $severity = $mentionsBurning ? 'purple' : 'orange';
        $electronicsWarnings = [
            'Kujdes nga mjetet elektronike dhe bateritë pasi kanë radioaktivitet dhe janë të rrezikshme.',
            'Paralajmërim: mbetjet elektronike dhe bateritë mund të jenë shumë të rrezikshme, shmangni kontaktin direkt.',
            'Kujdes i shtuar: pajisjet elektronike dhe bateritë e hedhura kërkojnë trajtim të veçantë për shkak të rrezikut.',
            'Mos i prekni pa mbrojtje mjetet elektronike dhe bateritë; mund të paraqesin rrezik serioz për shëndetin.',
            'Rrezik potencial: mbetjet elektronike dhe bateritë mund të lëshojnë substanca të dëmshme në mjedis.',
            'Kujdes maksimal: pajisjet elektronike të djegura ose të dëmtuara mund të jenë toksike dhe të pasigurta.',
            'Paralajmërim për sigurinë: bateritë dhe mjetet elektronike duhet të trajtohen vetëm me masa mbrojtëse.',
            'Shmangni ekspozimin: kontakti me mbetje elektronike dhe bateri të vjetra mund të jetë i rrezikshëm.',
            'Zona mund të përmbajë e-waste të rrezikshëm; mos i lëvizni pajisjet elektronike ose bateritë pa pajisje mbrojtëse.',
            'Kujdes: mbetjet elektronike dhe bateritë kërkojnë grumbullim të specializuar dhe nuk duhen trajtuar si mbetje të zakonshme.',
        ];
        $electronicsWarning = $electronicsWarnings[array_rand($electronicsWarnings)];

        if ($mentionsElectronics) {
            $rawWarnings = $rawWarnings !== ''
                ? $rawWarnings . ' ' . $electronicsWarning
                : $electronicsWarning;
        }

        $templates = [
            'djegie mbetjesh' => "Në këtë zonë ka shenja të djegies së mbetjeve dhe duhet shmangur qëndrimi i gjatë. Mos u afroni me zjarrin ose me mbetjet e ndezura, sepse tymi është i dëmshëm. Nëse është e sigurt, dokumentoni zonën nga distanca dhe mbani rrugët e aksesit të lira. Mos i shtoni mbetje të tjera dhe mos e nxisni zjarrin. Njoftoni shërbimet lokale ose komunën për ndërhyrje dhe pastrim. Nëse ka rrezik të përhapjes, kontaktoni urgjencën lokale.",
            'grumbullim mbetjesh' => "Identifikoni zonën me grumbullim mbetjesh dhe shmangni kontaktin e panevojshëm. Mblidhni mbetjet e dukshme në mënyrë të sigurt dhe ndani materialet nëse është e mundur. Hiqni papastërtitë e mëdha dhe mbajini të mbyllura në qese ose enë të veçanta. Mos i digjni mbeturinat, sepse krijojnë ndotje të ajrit dhe rrezik për shëndetin. Dorëzojini në pikat lokale të grumbullimit ose shërbimin komunal të pastrimit. Nëse është zonë problematike, dokumentojeni dhe njoftoni autoritetet lokale për ndërhyrje.",
            'ndotje e shpërndarë' => "Vlerësoni zonën me ndotje të shpërndarë dhe përcaktoni burimin kryesor. Mblidhni mbetjet e dukshme në mënyrë të sigurt dhe ndani materialet nëse është e mundur. Hiqni objektet e mëdha dhe mbajini në qese të mbyllura për transport. Mos i digjni mbeturinat, sepse krijojnë ndotje të ajrit dhe rrezik për shëndetin. Dorëzojini në pikat lokale të grumbullimit ose shërbimin komunal të pastrimit. Nëse ndotja përsëritet, dokumentojeni dhe njoftoni autoritetet lokale.",
            'ndotje e mundshme' => "Në këtë zonë ka shenja të ndotjes dhe duhet vlerësim i kujdesshëm në terren. Mblidhni mbetjet e dukshme në mënyrë të sigurt dhe ndani materialet nëse është e mundur. Hiqni papastërtitë e mëdha dhe mbajini në qese ose enë të mbyllura. Mos i digjni mbeturinat, sepse krijojnë ndotje të ajrit dhe rrezik për shëndetin. Dorëzojini në pikat lokale të grumbullimit ose shërbimin komunal të pastrimit. Nëse zona është problematike, dokumentojeni dhe njoftoni autoritetet lokale për ndërhyrje.",
        ];

        $instructions = $templates[$normalizedType] ?? $templates['ndotje e mundshme'];

        return [
            'item_type' => $normalizedType,
            'severity' => $severity,
            'recyclable' => null,
            'instructions' => $instructions,
            'warnings' => $rawWarnings !== '' ? $rawWarnings : null,
            'raw_output' => $responseJson,
            'model_name' => $responseJson['model'] ?? $model,
        ];
    }

    private static function extractText(array $response): ?string
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

    private static function parseJson(?string $text): array
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
