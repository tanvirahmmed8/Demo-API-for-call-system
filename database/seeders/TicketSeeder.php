<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        // Create sample tickets for each user
        foreach ($users as $user) {
            $statuses = ['open', 'in_progress', 'resolved', 'closed'];

            // Create 2-4 tickets per user
            $ticketCount = rand(2, 4);

            for ($i = 0; $i < $ticketCount; $i++) {
                Ticket::create([
                    'title' => 'Sample Ticket ' . ($i + 1),
                    'description' => 'This is a sample ticket description for testing purposes. This ticket was automatically generated.',
                    'user_id' => $user->id,
                    'ticket_number' => 'TKT-' . strtoupper(Str::random(8)),
                    'status' => $statuses[array_rand($statuses)],
                ]);
            }
        }
    }
}
