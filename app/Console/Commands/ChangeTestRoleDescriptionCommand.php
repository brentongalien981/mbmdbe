<?php

namespace App\Console\Commands;

use App\Models\Role;
use Illuminate\Console\Command;

class ChangeTestRoleDescriptionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'TestRoleDescription:Change';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change TestRole\'s description';

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
        $str = '';
        for ($i=0; $i < 10; $i++) { 
            $str .= rand(0, 100) . '-';
        }

        $r = Role::find(6);
        $r->description = $str;
        $r->save();
        
        return 0;
    }
}
