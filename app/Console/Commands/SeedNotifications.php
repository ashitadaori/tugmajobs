<?php

namespace App\Console\Commands;

use Database\Seeders\NotificationSeeder;
use Illuminate\Console\Command;

class SeedNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed sample notifications for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seeding notifications...');
        
        $seeder = new NotificationSeeder();
        $seeder->run();
        
        $this->info('Notifications seeded successfully!');
    }
}