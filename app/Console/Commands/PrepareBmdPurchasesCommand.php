<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PrepareBmdPurchasesCommand extends Command
{
    public const scheduledDispatchTime = '03:00';



    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'BmdPurchases:Prepare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepare BMD-Purchase-records.';

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
        return 0;
    }
}
