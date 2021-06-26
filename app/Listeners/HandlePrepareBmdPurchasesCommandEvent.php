<?php

namespace App\Listeners;

use App\Bmd\Constants\BmdGlobalConstants;
use Exception;
use App\Models\Role;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\ScheduledTask;
use App\Models\ScheduledTaskLog;
use App\Bmd\Generals\GeneralHelper;
use App\Models\ScheduledTaskStatus;
use App\Events\PrepareBmdPurchasesCommandEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandlePrepareBmdPurchasesCommandEvent implements ShouldQueue
{
    public $queue = BmdGlobalConstants::QUEUE_FOR_HANDLING_MANUAL_SCHEDULED_TASK_DISPATCHES;



    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PrepareBmdPurchasesCommandEvent  $event
     * @return void
     */
    public function handle(PrepareBmdPurchasesCommandEvent $event)
    {
        
        $d = $event->commandData;
        $executionStartTimeInSec = microtime(true);
        $resultMsg = 'Manually executing command in CLASS: HandlePrepareBmdPurchasesCommandEvent.\n';
        $isResultOk = false;


        $scheduledTask = ScheduledTask::find($d['jobId'])->get()[0];
        $scheduledTask->status_code = ScheduledTaskStatus::where('name', 'PROCESSING')->get()[0]->code;
        $scheduledTask->save();

        

        try {
            Purchase::prepareBmdPurchases($d['dateFrom'], $d['dateTo']);
            $resultMsg .= 'Executed METHOD: Purchase::prepareBmdPurchases().\n';

            Purchase::updateTodaysPurchasesStatus();
            $resultMsg .= 'Executed METHOD: Purchase::updateTodaysPurchasesStatus().\n';

            Order::updateOrdersStatusesWithDatePeriod($d['dateFrom'], $d['dateTo']);
            $resultMsg .= 'Executed METHOD: Order::updateOrdersStatusesWithDatePeriod(' . $d['dateFrom'] . ', ' . $d['dateTo'] . ').\n';

            $isResultOk = true;
        } catch (Exception $e) {
            $eLogStr = GeneralHelper::extractErrorTrace($e);
            $resultMsg .= $eLogStr . '\n';
        }


        $executionEndTimeInSec = microtime(true);
        $executionPeriod = $executionEndTimeInSec - $executionStartTimeInSec;

        $scheduleTaskLog = new ScheduledTaskLog();
        $scheduleTaskLog->scheduled_task_id = $scheduledTask->id;
        $scheduleTaskLog->execution_period = $executionPeriod;
        $scheduleTaskLog->status_code = $isResultOk ? ScheduledTaskStatus::where('name', 'PROCESS_SUCCEEDED')->get()[0]->code : ScheduledTaskStatus::where('name', 'PROCESS_FAILED')->get()[0]->code;
        $scheduleTaskLog->is_successful = $isResultOk ? 1 : 0;
        $scheduleTaskLog->entire_process_logs = $resultMsg;
        $scheduleTaskLog->save();


        $scheduledTask->status_code = ScheduledTaskStatus::where('name', 'AVAILABLE')->get()[0]->code;
        $scheduledTask->save();
    }
}
