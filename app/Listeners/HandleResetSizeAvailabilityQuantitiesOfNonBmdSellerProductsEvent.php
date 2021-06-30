<?php

namespace App\Listeners;

use Exception;
use App\Models\Role;
use App\Models\ProductSeller;
use App\Models\ScheduledTask;
use App\Models\ScheduledTaskLog;
use App\Bmd\Generals\GeneralHelper;
use App\Models\ScheduledTaskStatus;
use Illuminate\Queue\InteractsWithQueue;
use App\Bmd\Constants\BmdGlobalConstants;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\ResetSizeAvailabilityQuantitiesOfNonBmdSellerProductsEvent;

class HandleResetSizeAvailabilityQuantitiesOfNonBmdSellerProductsEvent implements ShouldQueue
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
     * @param  object  $event
     * @return void
     */
    public function handle(ResetSizeAvailabilityQuantitiesOfNonBmdSellerProductsEvent $event)
    {
        $executionStartTimeInSec = microtime(true);
        $resultMsg = 'Manually executing command in CLASS: HandleResetSizeAvailabilityQuantitiesOfNonBmdSellerProductsEvent.\n';
        $isResultOk = false;

        $d = $event->commandData;
        $scheduledTask = ScheduledTask::find($d['jobId']);
        $scheduledTask->status_code = ScheduledTaskStatus::where('name', 'PROCESSING')->get()[0]->code;
        $scheduledTask->save();


        try {

            ProductSeller::resetSizeAvailabilityQuantitiesOfNonBmdSellerProducts();
            $resultMsg .= 'Executed METHOD: ProductSeller::resetSizeAvailabilityQuantitiesOfNonBmdSellerProducts().\n';

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
