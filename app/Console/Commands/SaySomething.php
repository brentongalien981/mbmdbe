<?php

namespace App\Console\Commands;

use App\Models\Role;
use Illuminate\Console\Command;

class SaySomething extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'say:something';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $newRole = new Role();
        $newRole->name = 'TestRole2';
        $newRole->save();

        return 1;
    }
}
