<?php

namespace App\Events;

use App\Bmd\Constants\BmdExceptions;
use App\Bmd\Constants\BmdGlobalConstants;
use App\Listeners\HandleGenerateOPIsEvent;
use Exception;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class GenerateOPIsEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public const TREND_PERIOD_OPTION_DAILY = 1;
    public const TREND_PERIOD_OPTION_WEEKLY = 2;
    public const TREND_PERIOD_OPTION_MONTHLY = 3;
    public const TREND_PERIOD_OPTION_YEARLY = 4;

    public const TREND_CHANGE_OPTION_INCREASING = 1;
    public const TREND_CHANGE_OPTION_DECREASING = 2;
    public const TREND_CHANGE_OPTION_AVERAGE = 3;
    public const TREND_CHANGE_OPTION_INCREAS_AND_DECREASE = 4;


    
    public $commandData;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($commandData)
    {
        $this->commandData = $commandData;
    }



    public static function guardForProductionEnv() {

        $appEnv = env('APP_ENV');

        switch ($appEnv) {
            case 'staging':
            case 'production':
            case 'deployment':
                $exceptionMsg = 'BMD Exception: Command Not Allowed In Production Mode';
                throw new Exception($exceptionMsg);
                break;
        }
    }



    public static function guardTooManyOrdersToBeCreated($commandData)
    {
        $ithDayOfTheYearForStartDate = getdate(strtotime($commandData['dateFrom']))['yday'];
        $ithDayOfTheYearForEndDate = getdate(strtotime($commandData['dateTo']))['yday'];

        $periodDays = $ithDayOfTheYearForEndDate - $ithDayOfTheYearForStartDate + 1;        
        $maxNumOfDailyOrders = $commandData['maxBaseNumOfDailyOrders'];

        // Basic guard.
        if (($periodDays * $maxNumOfDailyOrders) > BmdGlobalConstants::MAX_NUM_OF_FAKE_ORDERS_TO_BE_GENERATED_PER_SCHEDULED_TASK) {
            $exceptionMsg = 'BMD Exception: ' . BmdExceptions::MAX_NUM_OF_FAKE_ORDERS_TO_BE_GENERATED_PER_SCHEDULED_TASK['id'];
            $exceptionMsg .= ' => ' . BmdExceptions::MAX_NUM_OF_FAKE_ORDERS_TO_BE_GENERATED_PER_SCHEDULED_TASK['name'];
            throw new Exception($exceptionMsg);
        }



        // Detailed guard.
        $totalProjectedOrders = 0;
        $numOfDaysInSelectedPeriod = HandleGenerateOPIsEvent::getNumOfDaysInPeriod($commandData['trendPeriod']);

        // $numOfDayLeft is the remaining days from start to finish of OPI generation.

        for ($numOfDayLeft = $periodDays; $numOfDayLeft > 0; $numOfDayLeft -= $numOfDaysInSelectedPeriod) { 
            $totalProjectedOrders += $numOfDaysInSelectedPeriod * $maxNumOfDailyOrders;

            if ($totalProjectedOrders > BmdGlobalConstants::MAX_NUM_OF_FAKE_ORDERS_TO_BE_GENERATED_PER_SCHEDULED_TASK) {
                $exceptionMsg = 'BMD Exception: ' . BmdExceptions::MAX_NUM_OF_FAKE_ORDERS_TO_BE_GENERATED_PER_SCHEDULED_TASK['id'];
                $exceptionMsg .= ' => ' . BmdExceptions::MAX_NUM_OF_FAKE_ORDERS_TO_BE_GENERATED_PER_SCHEDULED_TASK['name'];
                throw new Exception($exceptionMsg);
            }

            $percent = $commandData['trendChangePercentage'];
            $maxNumOfDailyOrders *= (1.0 + ($percent / 100.0));
        }
    }



    public static function extractCommandValidatedData(Request $r)
    {

        $v = $r->validate([
            'dateFrom' => 'date',
            'dateTo' => 'date',
            'maxBaseNumOfDailyOrders' => 'integer|min:1',
            'trendChangePercentage' => 'numeric|min:0',
            'trendChange' => 'integer',
            'trendPeriod' => 'integer'
        ]);


        $commandData['jobId'] = $r->jobId;
        $commandData['dateFrom'] = $v['dateFrom'];
        $commandData['dateTo'] = $v['dateTo'];
        $commandData['maxBaseNumOfDailyOrders'] = $v['maxBaseNumOfDailyOrders'];
        $commandData['trendChangePercentage'] = $v['trendChangePercentage'];
        $commandData['trendChange'] = $v['trendChange'];
        $commandData['trendPeriod'] = $v['trendPeriod'];

        return $commandData;
    }
}
