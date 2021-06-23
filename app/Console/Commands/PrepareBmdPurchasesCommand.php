<?php

namespace App\Console\Commands;

use App\Bmd\Generals\GeneralHelper;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\ScheduledTaskLog;
use Exception;
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
        $executionStartTimeInSec = microtime(true);
        $resultMsg = '';
        $isResultOk = 0;


        $numOfSecInDay = 86400;
        $dateObjToday = getdate();
        $dateObjYesterday = getdate($dateObjToday[0] - $numOfSecInDay);

        $startDateObj = getdate($dateObjYesterday[0]);
        $endDataObj = getdate($dateObjYesterday[0]);

        $ordersStartDateInStr = $startDateObj['year'] . '-' . $startDateObj['mon'] . '-' . $startDateObj['mday'];
        $ordersEndDateInStr = $endDataObj['year'] . '-' . $endDataObj['mon'] . '-' . $endDataObj['mday'];


        try {
            Purchase::prepareBmdPurchases($ordersStartDateInStr, $ordersEndDateInStr);
            $resultMsg .= 'Executed Purchase::prepareBmdPurchases().\n';

            Purchase::updateTodaysPurchasesStatus();
            $resultMsg .= 'Executed Purchase::updateTodaysPurchasesStatus().\n';
    
            Order::updateYesterdaysOrdersStatus();
            $resultMsg .= 'Executed Order::updateYesterdaysOrdersStatus().\n';

            $isResultOk = 1;

        } catch (Exception $e) {
            $numOfErrorLines = 4;
            $eLogStr = GeneralHelper::extractErrorTrace($e, $numOfErrorLines);

            $resultMsg .= $eLogStr . '\n';
        }


        $executionEndTimeInSec = microtime(true);
        $executionPeriod = $executionEndTimeInSec - $executionStartTimeInSec;

        $scheduleTaskLog = new ScheduledTaskLog();
        $scheduleTaskLog->command_signature = $this->signature;
        $scheduleTaskLog->execution_period = $executionPeriod;
        $scheduleTaskLog->is_successful = $isResultOk;
        $scheduleTaskLog->entire_process_logs = $this->resultMsg;
        $scheduleTaskLog->save();


        return 0;
    }

}
