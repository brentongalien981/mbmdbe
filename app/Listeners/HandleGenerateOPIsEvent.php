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
use Database\Factories\OrderFactory;
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
        $ithDayOfTheYearForStartDate = getdate(strtotime($d['dateFrom']))['yday'];
        $ithDayOfTheYearForEndDate = getdate(strtotime($d['dateTo']))['yday'];

        $numDays = $ithDayOfTheYearForEndDate - $ithDayOfTheYearForStartDate;

        $maxBaseNumOfDailyOrders = $d['maxBaseNumOfDailyOrders'];
        $numOfDaysInPeriod = $this->getNumOfDaysInPeriod($d['trendPeriod']);
        $maxNumOrdersForCurrentPeriod = $maxBaseNumOfDailyOrders;


        for ($i = 0; $i < $numDays; $i++) {

            // if the day is the end of the trend-period, set the max-num-of-orders-for-that-period
            // depending on trend-change and trend-change-percentage
            if (($i != 0) && ($i % $numOfDaysInPeriod == 0)) {

                $maxNumOrdersForCurrentPeriod = $this->getMaxNumOrdersForPeriod($maxNumOrdersForCurrentPeriod, $d['trendChangePercentage'], $d['trendChange']);
            }

            // create fake-orders for that day
            $date = GeneralHelper::getDateInStrWithData($d['dateFrom'], $i);
            OrderFactory::generateFakeBmdOrders($date, $maxNumOrdersForCurrentPeriod);
        }




        /** Generate purchases. */



        /** Update invetory-items. */
    }



    private function getNumOfDaysInPeriod($trendPeriod)
    {
        switch ($trendPeriod) {
            case GenerateOPIsEvent::TREND_PERIOD_OPTION_DAILY:
                return 1;
            case GenerateOPIsEvent::TREND_PERIOD_OPTION_WEEKLY:
                return 7;
            case GenerateOPIsEvent::TREND_PERIOD_OPTION_MONTHLY:
                return 30;
            case GenerateOPIsEvent::TREND_PERIOD_OPTION_YEARLY:
                return 364;
            default:
                return 1;
        }
    }



    private function getMaxNumOrdersForPeriod($maxNumOrdersForCurrentPeriod, $trendChangePercentage, $trendChange)
    {
        $numOrdersToBeAddedOrDeleted = 0;
        $trendChangeScale = $trendChangePercentage / 100.0;

        switch ($trendChange) {
            case GenerateOPIsEvent::TREND_CHANGE_OPTION_INCREASING:
                $numOrdersToBeAddedOrDeleted = $maxNumOrdersForCurrentPeriod * $trendChangeScale;
                break;
            case GenerateOPIsEvent::TREND_CHANGE_OPTION_DECREASING:
                $numOrdersToBeAddedOrDeleted = $maxNumOrdersForCurrentPeriod * $trendChangeScale * -1;
                break;
            case GenerateOPIsEvent::TREND_CHANGE_OPTION_AVERAGE:
                $numOrdersToBeAddedOrDeleted = 0;
                break;
            case GenerateOPIsEvent::TREND_CHANGE_OPTION_INCREAS_AND_DECREASE:
                if (rand(0, 1)) {
                    $numOrdersToBeAddedOrDeleted = $maxNumOrdersForCurrentPeriod * $trendChangeScale;
                } else {
                    $numOrdersToBeAddedOrDeleted = $maxNumOrdersForCurrentPeriod * $trendChangeScale * -1;
                }
                break;
        }


        return $maxNumOrdersForCurrentPeriod + round($numOrdersToBeAddedOrDeleted);
    }
}
