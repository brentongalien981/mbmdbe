<?php

namespace App\Listeners;

use Exception;
use App\Models\ScheduledTask;
use App\Models\ScheduledTaskLog;
use App\Events\GenerateOPIsEvent;
use App\Bmd\Generals\GeneralHelper;
use App\Models\ScheduledTaskStatus;
use Illuminate\Queue\InteractsWithQueue;
use App\Bmd\Constants\BmdGlobalConstants;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleGenerateOPIsEvent implements ShouldQueue
{
    public $queue = BmdGlobalConstants::QUEUE_FOR_HANDLING_MANUAL_SCHEDULED_TASK_DISPATCHES;

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(GenerateOPIsEvent $event)
    {
        $executionStartTimeInSec = microtime(true);
        $resultMsg = 'Manually executing command in CLASS: ' . self::class . ' .\n';
        $isResultOk = false;

        $d = $event->commandData;
        $scheduledTask = ScheduledTask::find($d['jobId']);
        $scheduledTask->status_code = ScheduledTaskStatus::where('name', 'PROCESSING')->get()[0]->code;
        $scheduledTask->save();


        try {

            $this->generateOPIs($d);
            $resultMsg .= 'Executed METHOD: generateOPIs().\n';

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



    private function generateOPIs($d)
    {

        /** Generate orders. */
        // calculate the number of days

        // loop through the num of days

        //     set the max-num-of-orders-for-that-period

        //     if the day is the end of the trend-period, set the max-num-of-orders-for-that-period
        //     depending on trend-change and trend-change-percentage

        //     create fake-orders for that day



        /** Generate purchases. */


        /** Update invetory-items. */
    }
}
