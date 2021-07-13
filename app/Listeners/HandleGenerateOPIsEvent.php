<?php

namespace App\Listeners;

use Exception;
use Throwable;
use App\Models\Purchase;
use App\Models\ScheduledTask;
use App\Models\ScheduledTaskLog;
use App\Events\GenerateOPIsEvent;
use App\Bmd\Generals\GeneralHelper;
use App\Models\ScheduledTaskStatus;
use Database\Factories\OrderFactory;
use Illuminate\Queue\InteractsWithQueue;
use App\Bmd\Constants\BmdGlobalConstants;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleGenerateOPIsEvent implements ShouldQueue
{
    public $failOnTimeout = true;
    public $queue = BmdGlobalConstants::QUEUE_FOR_HANDLING_LONG_MANUAL_SCHEDULED_TASK_DISPATCHES;



    public $myScheduledTask = null;
    public $myScheduleTaskLog = null;
    public $executionStartTimeInSec = 0;
    public $defaultMsg = 'Manually executing command in CLASS: ' . self::class . ' .\n';
    public $isResultOk = false;

    public $commandData = null;
    private $numDaysForOrderCreation = 0;



    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(GenerateOPIsEvent $event)
    {

        $this->executionStartTimeInSec = microtime(true);
        $d = $event->commandData;
        $this->commandData = $d;

        $this->myScheduledTask = ScheduledTask::find($d['jobId']);
        $this->myScheduledTask->status_code = ScheduledTaskStatus::where('name', 'PROCESSING')->get()[0]->code;
        $this->myScheduledTask->save();

        $extraMsg = '';


        try {
            $this->initScheduledTaskLog();
            $this->generateOPIs($d);
            $extraMsg .= 'Executed METHOD: generateOPIs().\n';

            $this->isResultOk = true;
        } catch (Exception $e) {
            $eLogStr = GeneralHelper::extractErrorTrace($e);
            $extraMsg .= $eLogStr . '\n';
        }

        $this->updateLogs([
            'extraMsg' => $extraMsg,
            'isForFinalizationUpdate' => true
        ]);


        $this->myScheduledTask->status_code = ScheduledTaskStatus::where('name', 'AVAILABLE')->get()[0]->code;
        $this->myScheduledTask->save();
    }



    public function initScheduledTaskLog()
    {
        $partialExecutionEndTimeInSec = microtime(true);
        $partialExecutionPeriod = $partialExecutionEndTimeInSec - $this->executionStartTimeInSec;


        $this->myScheduleTaskLog = new ScheduledTaskLog();
        $this->myScheduleTaskLog->scheduled_task_id = $this->myScheduledTask->id;
        $this->myScheduleTaskLog->execution_period = $partialExecutionPeriod;
        $this->myScheduleTaskLog->status_code = ScheduledTaskStatus::where('name', 'PROCESSING')->get()[0]->code;
        $this->myScheduleTaskLog->is_successful = 1;
        $this->myScheduleTaskLog->entire_process_logs = $this->getBaseLogs();
        $this->myScheduleTaskLog->save();
    }



    public function updateLogs($data)
    {
        $partialExecutionEndTimeInSec = microtime(true);
        $partialExecutionPeriod = $partialExecutionEndTimeInSec - $this->executionStartTimeInSec;

        $msg = $this->getBaseLogs();

        if (isset($data['isForCheckpointUpdate'])) {

            if (isset($data['ithDayOfOrderCreation'])) {
                $msg .= 'num of order days creation processed: ' . $data['ithDayOfOrderCreation'] . ' / ' . $this->numDaysForOrderCreation . ' \n';
            }

            if (isset($data['ithDayOfPurchaseCreation'])) {
                $msg .= 'num of purchase days creation processed: ' . $data['ithDayOfPurchaseCreation'] . ' / ' . $this->numDaysForOrderCreation . ' \n';
            }
        } else if ($data['isForFinalizationUpdate']) {

            $this->myScheduleTaskLog->execution_period = $partialExecutionPeriod;
            $this->myScheduleTaskLog->status_code = $this->isResultOk ? ScheduledTaskStatus::where('name', 'PROCESS_SUCCEEDED')->get()[0]->code : ScheduledTaskStatus::where('name', 'PROCESS_FAILED')->get()[0]->code;
            $this->myScheduleTaskLog->is_successful = $this->isResultOk ? 1 : 0;

            $msg = $this->myScheduleTaskLog->entire_process_logs;
            $msg .= $data['extraMsg'];
        }


        $this->myScheduleTaskLog->execution_period = $partialExecutionPeriod;
        $this->myScheduleTaskLog->entire_process_logs = $msg;
        $this->myScheduleTaskLog->save();

        return $msg;
    }



    private function getBaseLogs()
    {
        $msg = $this->defaultMsg;
        $msg .= 'startDate: ' . $this->commandData['dateFrom'] . '\n';
        $msg .= 'endDate: ' . $this->commandData['dateTo'] . '\n';
        $msg .= 'maxBaseNumOfDailyOrders: ' . $this->commandData['maxBaseNumOfDailyOrders'] . '\n';
        $msg .= 'trendChangePercentage: ' . $this->commandData['trendChangePercentage'] . '\n';
        $msg .= 'trendChange: ' . $this->commandData['trendChange'] . '\n';
        $msg .= 'trendChange: ' . $this->commandData['trendChange'] . '\n';

        return $msg;
    }



    private function generateOPIs($d)
    {

        /** Generate orders. */
        // calculate the number of days
        $ithDayOfTheYearForStartDate = getdate(strtotime($d['dateFrom']))['yday'];
        $ithDayOfTheYearForEndDate = getdate(strtotime($d['dateTo']))['yday'];

        $this->numDaysForOrderCreation = $ithDayOfTheYearForEndDate - $ithDayOfTheYearForStartDate + 1;

        $maxBaseNumOfDailyOrders = $d['maxBaseNumOfDailyOrders'];
        $numOfDaysInPeriod = $this->getNumOfDaysInPeriod($d['trendPeriod']);
        $maxNumOrdersForCurrentPeriod = $maxBaseNumOfDailyOrders;


        for ($i = 0; $i < $this->numDaysForOrderCreation; $i++) {

            // if the day is the end of the trend-period, set the max-num-of-orders-for-that-period
            // depending on trend-change and trend-change-percentage
            if (($i != 0) && ($i % $numOfDaysInPeriod == 0)) {

                $maxNumOrdersForCurrentPeriod = $this->getMaxNumOrdersForPeriod($maxNumOrdersForCurrentPeriod, $d['trendChangePercentage'], $d['trendChange']);
            }

            // create fake-orders for that day
            $date = GeneralHelper::getDateInStrWithData($d['dateFrom'], $i);
            OrderFactory::generateFakeBmdOrders($date, $maxNumOrdersForCurrentPeriod);

            $this->updateLogs([
                'ithDayOfOrderCreation' => $i + 1,
                'isForCheckpointUpdate' => true
            ]);
        }


        Purchase::generateBmdPurchases($d['dateFrom'], $d['dateTo'], $this);
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



    public function failed(Throwable $e)
    {
        $st = ScheduledTask::where('command_signature', 'GenerateOPIs:Execute')->get()[0];
        $stLog = ScheduledTaskLog::where('scheduled_task_id', $st->id)->latest()->get()[0];

        $stLog->status_code = ScheduledTaskStatus::where('name', 'PROCESS_FAILED')->get()[0]->code;
        $stLog->is_successful = 0;

        $eLogStr = GeneralHelper::extractErrorTrace($e);
        $stLog->entire_process_logs .= $eLogStr . '\n';
        $stLog->save();

    }
}
