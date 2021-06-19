<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OneTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $this->call([
            OrderItemStatusSeeder::class,
            PurchaseStatusSeeder::class,
            PurchaseItemStatusSeeder::class,
            InventoryItemStatusSeeder::class
        ]);
    }
}
