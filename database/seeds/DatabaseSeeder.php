<?php

use App\Models\Reference;
use App\Models\Supplier;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        \App\Models\User::insert([
            'name' => 'Admin',
            'email' => 'manish@journal.com',
            'password' => Hash::make('u4GZzgmNpKNTKU3QMYRQ2#3R')
        ]);

        \App\Models\Reference::insert([
            'name' => 'Walk In'
        ]);

        $faker = Faker::create();

        for ($i = 1; $i <= 5; $i++) {
            Supplier::create([
                'name' => $faker->randomElement(['Global', 'Express', 'Adventures', 'Discover', 'Journey', 'Explore', 'Voyage', 'Destination']) . ' Travels',
                'total_payable' => 0.00,
            ]);
        }

        for ($i = 1; $i <= 5; $i++) {
            Reference::create([
                'name' => $faker->name,
            ]);
        }

        $supplierIds = Supplier::pluck('id')->all();

        // Get all reference IDs
        $referenceIds = Reference::pluck('id')->all();

        // Generate random tickets
        for ($i = 1; $i <= 25; $i++) {
            $supplierId = $supplierIds[array_rand($supplierIds)];
            $referenceId = $referenceIds[array_rand($referenceIds)];

            $departureDate = $faker->dateTimeBetween('-30 days', '+30 days');
            $returnDate = $faker->dateTimeBetween($departureDate, '+30 days');

            $ticket = Ticket::create([
                'ticket_number' => $this->generateUniqueTicketNumber(),
                'created_at' => $faker->dateTimeBetween('-7 days', 'now'),
                'departure_date' => $departureDate,
                'return_date' => $returnDate,
                'customer_name' => $faker->name,
                'supplier_id' => $supplierId,
                'reference_id' => $referenceId,
                'total' => rand(100, 500),
                'profit' => rand(20, 100),
                'cost' => rand(50, 200),
            ]);

            // Update supplier's total_payable
            $supplier = Supplier::find($supplierId);
            $supplier->total_payable += $ticket->cost;
            $supplier->save();
        }
    }

    private function generateUniqueTicketNumber()
    {
        $ticketNumber = mt_rand(1000000000000, 9999999999999);

        // Check if the generated ticket number already exists
        while (Ticket::where('ticket_number', $ticketNumber)->exists()) {
            $ticketNumber = mt_rand(1000000000000, 9999999999999);
        }

        return $ticketNumber;
    }
}
