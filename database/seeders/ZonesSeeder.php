<?php

namespace Database\Seeders;

use App\Enums\Severity;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZonesSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            ['name' => 'Tirana', 'polygon' => $this->rect(41.36, 19.72, 41.28, 19.92)],
            ['name' => 'Tirana – Qendër', 'polygon' => $this->rect(41.34, 19.79, 41.31, 19.84)],
            ['name' => 'Tirana – Blloku', 'polygon' => $this->rect(41.33, 19.80, 41.315, 19.83)],
            ['name' => 'Tirana – Kombinat', 'polygon' => $this->rect(41.35, 19.75, 41.32, 19.79)],
            ['name' => 'Tirana – Laprakë', 'polygon' => $this->rect(41.35, 19.80, 41.33, 19.83)],
            ['name' => 'Tirana – Ali Demi', 'polygon' => $this->rect(41.33, 19.82, 41.31, 19.85)],
            ['name' => 'Tirana – Kinostudio', 'polygon' => $this->rect(41.34, 19.84, 41.32, 19.88)],
            ['name' => 'Tirana – Don Bosko', 'polygon' => $this->rect(41.33, 19.78, 41.31, 19.81)],
            ['name' => 'Tirana – Yzberisht', 'polygon' => $this->rect(41.32, 19.75, 41.30, 19.78)],

            ['name' => 'Durrës', 'polygon' => $this->rect(41.34, 19.40, 41.26, 19.52)],
            ['name' => 'Durrës – Qendër', 'polygon' => $this->rect(41.32, 19.43, 41.30, 19.46)],
            ['name' => 'Durrës – Plazh', 'polygon' => $this->rect(41.31, 19.45, 41.27, 19.50)],
            ['name' => 'Durrës – Porti', 'polygon' => $this->rect(41.32, 19.43, 41.30, 19.45)],

            ['name' => 'Vlorë', 'polygon' => $this->rect(40.50, 19.42, 40.42, 19.52)],
            ['name' => 'Vlorë – Qendër', 'polygon' => $this->rect(40.48, 19.47, 40.45, 19.49)],
            ['name' => 'Vlorë – Lungomare', 'polygon' => $this->rect(40.46, 19.47, 40.43, 19.51)],
            ['name' => 'Vlorë – Skelë', 'polygon' => $this->rect(40.46, 19.46, 40.44, 19.48)],

            ['name' => 'Shkodër', 'polygon' => $this->rect(42.10, 19.44, 42.02, 19.53)],
            ['name' => 'Shkodër – Qendër', 'polygon' => $this->rect(42.07, 19.49, 42.05, 19.52)],
            ['name' => 'Shkodër – Parrucë', 'polygon' => $this->rect(42.06, 19.47, 42.04, 19.50)],

            ['name' => 'Elbasan', 'polygon' => $this->rect(41.14, 20.05, 41.06, 20.14)],
            ['name' => 'Elbasan – Qendër', 'polygon' => $this->rect(41.12, 20.07, 41.10, 20.10)],

            ['name' => 'Fier', 'polygon' => $this->rect(40.76, 19.52, 40.70, 19.58)],
            ['name' => 'Fier – Qendër', 'polygon' => $this->rect(40.74, 19.55, 40.72, 19.57)],

            ['name' => 'Korçë', 'polygon' => $this->rect(40.65, 20.73, 40.60, 20.80)],
            ['name' => 'Korçë – Qendër', 'polygon' => $this->rect(40.63, 20.77, 40.61, 20.79)],

            ['name' => 'Berat', 'polygon' => $this->rect(40.73, 19.93, 40.69, 19.98)],
            ['name' => 'Berat – Qendër Historike', 'polygon' => $this->rect(40.72, 19.94, 40.70, 19.96)],

            ['name' => 'Gjirokastër', 'polygon' => $this->rect(40.09, 20.11, 40.04, 20.16)],

            ['name' => 'Lezhë', 'polygon' => $this->rect(41.80, 19.61, 41.77, 19.66)],
            ['name' => 'Lezhë – Qendër', 'polygon' => $this->rect(41.79, 19.63, 41.78, 19.65)],

            ['name' => 'Sarandë', 'polygon' => $this->rect(39.89, 20.00, 39.84, 20.06)],
            ['name' => 'Sarandë – Qendër', 'polygon' => $this->rect(39.88, 20.01, 39.86, 20.03)],
            ['name' => 'Sarandë – Port', 'polygon' => $this->rect(39.88, 20.00, 39.86, 20.02)],
        ];

        foreach ($zones as $zone) {
            Zone::query()->updateOrCreate(
                ['name' => $zone['name']],
                [
                    'polygon' => $zone['polygon'],
                    'current_severity' => Severity::Green,
                ]
            );
        }
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
