<?php

namespace App\Console\Commands;

use App\Models\Purchase;
use Illuminate\Console\Command;

class PrepareBmdPurchasesCommand extends Command
{
    public const scheduledDispatchTime = '03:05';




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

        $numOfSecInDay = 86400;
        $dateObjToday = getdate();
        // $dateObjYesterday = getdate($dateObjToday[0] - $numOfSecInDay);

        $startDateObj = getdate($dateObjToday[0]);
        $endDataObj = getdate($dateObjToday[0]);

        $ordersStartDateInStr = $startDateObj['year'] . '-' . $startDateObj['mon'] . '-' . $startDateObj['mday'];
        $ordersEndDateInStr = $endDataObj['year'] . '-' . $endDataObj['mon'] . '-' . $endDataObj['mday'];


        Purchase::prepareBmdPurchases($ordersStartDateInStr, $ordersEndDateInStr);

        Purchase::updateTodaysPurchasesStatus();

        return 0;
    }

}
