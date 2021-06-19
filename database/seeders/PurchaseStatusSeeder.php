<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('purchase_status')->insert(['code' => -304, 'name' => 'PURCHASE_INCOMPLETELY_RECEIVED']);
        DB::table('purchase_status')->insert(['code' => -301, 'name' => 'EVALUATED_INCOMPLETELY_FOR_PURCHASE']);

        DB::table('purchase_status')->insert(['code' => 300, 'name' => 'DEFAULT']);
        DB::table('purchase_status')->insert(['code' => 301, 'name' => 'TO_BE_PURCHASED']);
        DB::table('purchase_status')->insert(['code' => 302, 'name' => 'PURCHASED']);
        DB::table('purchase_status')->insert(['code' => 303, 'name' => 'TO_BE_PURCHASE_RECEIVED']);
        DB::table('purchase_status')->insert(['code' => 304, 'name' => 'PURCHASE_RECEIVED']);
        DB::table('purchase_status')->insert(['code' => 305, 'name' => 'IN_STOCK']);

        DB::table('purchase_status')->insert(['code' => 306, 'name' => 'TO_BE_PACKAGED']);
        DB::table('purchase_status')->insert(['code' => 307, 'name' => 'PACKAGED']);

        DB::table('purchase_status')->insert(['code' => 308, 'name' => 'TO_BE_DISPATCHED']);
        DB::table('purchase_status')->insert(['code' => 309, 'name' => 'DISPATCHED']);
    }
}
