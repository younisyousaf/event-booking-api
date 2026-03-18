<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        // Re-use an existing admin user as the event creator when possible
        $admin = User::where('email', 'admin@example.com')->first();

        if (! $admin) {
            $admin = User::factory()->create([
                'name'  => 'Admin User',
                'email' => 'admin@example.com',
            ]);
        }

        // 15 upcoming events owned by admin
        Event::factory(15)->create(['created_by' => $admin->id]);

        // 5 past events for historical data
        Event::factory(5)->past()->create(['created_by' => $admin->id]);

        // 2 sold-out upcoming events
        Event::factory(2)->soldOut()->create(['created_by' => $admin->id]);

        $this->command->info('EventSeeder: 22 events created.');
    }
}