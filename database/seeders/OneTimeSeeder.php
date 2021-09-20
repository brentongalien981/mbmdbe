<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OneTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('order_item_status')->insert(['code' => 310, 'name' => 'BEING_PACKAGED']);

        DB::table('order_item_status')->insert(['code' => -310, 'name' => 'MISSING_ORDER_ITEM']);
        DB::table('order_item_status')->insert(['code' => -311, 'name' => 'BROKEN_ORDER_ITEM']);
        DB::table('order_item_status')->insert(['code' => -312, 'name' => 'TOO_LATE_TO_DELIVER']);
        DB::table('order_item_status')->insert(['code' => -313, 'name' => 'TOO_EXPENSIVE_TO_DELIVER']);
        DB::table('order_item_status')->insert(['code' => -314, 'name' => 'OTHER_ORDER_ITEM_PROBLEMS']);
    }
}
