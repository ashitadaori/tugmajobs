<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Job;

class UpdateJobCoordinatesSeeder extends Seeder
{
    /**
     * Update existing jobs with coordinates for map visualization
     */
    public function run()
    {
        $this->command->info('Updating jobs with coordinates...');

        // Corrected coordinates for Santa Cruz, Davao del Sur barangays
        // Santa Cruz town center is approximately at 6.8370, 125.4130
        $barangays = [
            'Poblacion' => ['lat' => 6.8375, 'lng' => 125.4125],
            'Zone I' => ['lat' => 6.8390, 'lng' => 125.4110],
            'Zone II' => ['lat' => 6.8365, 'lng' => 125.4140],
            'Zone III' => ['lat' => 6.8350, 'lng' => 125.4120],
            'Zone IV' => ['lat' => 6.8380, 'lng' => 125.4150],
            'Astorga' => ['lat' => 6.8520, 'lng' => 125.3980],
            'Bato' => ['lat' => 6.8150, 'lng' => 125.4050],
            'Coronon' => ['lat' => 6.8450, 'lng' => 125.3900],
            'Darong' => ['lat' => 6.8600, 'lng' => 125.4200],
            'Inawayan' => ['lat' => 6.7950, 'lng' => 125.4300],
            'Jose Rizal' => ['lat' => 6.8100, 'lng' => 125.4180],
            'Matutungan' => ['lat' => 6.8250, 'lng' => 125.3950],
            'Melilia' => ['lat' => 6.8550, 'lng' => 125.4050],
            'Saliducon' => ['lat' => 6.8650, 'lng' => 125.4100],
            'Sibulan' => ['lat' => 6.8000, 'lng' => 125.4150],
            'Sinoron' => ['lat' => 6.7900, 'lng' => 125.4200],
            'Tagabuli' => ['lat' => 6.8700, 'lng' => 125.4000],
            'Tibolo' => ['lat' => 6.8200, 'lng' => 125.4250],
            'Tuban' => ['lat' => 6.7850, 'lng' => 125.4100],
            'Santa Cruz Proper' => ['lat' => 6.8370, 'lng' => 125.4130],
        ];

        $barangayNames = array_keys($barangays);
        $updated = 0;

        foreach (Job::all() as $job) {
            $brgyName = $barangayNames[array_rand($barangayNames)];
            $coords = $barangays[$brgyName];

            // Add slight variation to coordinates for visual spread
            $lat = $coords['lat'] + (rand(-50, 50) / 10000);
            $lng = $coords['lng'] + (rand(-50, 50) / 10000);

            $job->update([
                'barangay' => $brgyName,
                'latitude' => $lat,
                'longitude' => $lng,
            ]);
            $updated++;
        }

        $this->command->info("Updated {$updated} jobs with coordinates!");

        // Show distribution
        $distribution = Job::whereNotNull('barangay')
            ->selectRaw('barangay, COUNT(*) as count')
            ->groupBy('barangay')
            ->orderByDesc('count')
            ->get();

        $this->command->info('');
        $this->command->info('Job distribution by barangay:');
        foreach ($distribution as $row) {
            $this->command->info("  {$row->barangay}: {$row->count} jobs");
        }
    }
}
