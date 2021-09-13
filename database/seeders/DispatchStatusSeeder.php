<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DispatchStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('dispatch_statuses')->insert(['code' => -504, 'name' => 'EP_BATCH_CREATION_FAILED']);
        DB::table('dispatch_statuses')->insert(['code' => -501, 'name' => 'OTHER_ERRORS']);

        DB::table('dispatch_statuses')->insert(['code' => 500, 'name' => 'DEFAULT']);
        DB::table('dispatch_statuses')->insert(['code' => 501, 'name' => 'EP_BATCH_CREATING']);
        DB::table('dispatch_statuses')->insert(['code' => 502, 'name' => 'EP_BATCH_CREATED']);
        DB::table('dispatch_statuses')->insert(['code' => 503, 'name' => 'EP_BATCH_UPDATED']);
        DB::table('dispatch_statuses')->insert(['code' => 504, 'name' => 'EP_BATCH_LABELS_GENERATED']);
        DB::table('dispatch_statuses')->insert(['code' => 505, 'name' => 'EP_BATCH_SCANFORM_GENERATED']);
        DB::table('dispatch_statuses')->insert(['code' => 506, 'name' => 'DISPATCHING']);
        DB::table('dispatch_statuses')->insert(['code' => 507, 'name' => 'DISPATCHED']);
        DB::table('dispatch_statuses')->insert(['code' => 508, 'name' => 'CANCELLED']);
    }
}
