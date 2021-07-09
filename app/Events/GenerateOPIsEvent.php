<?php

namespace App\Events;

use App\Bmd\Constants\BmdExceptions;
use App\Bmd\Constants\BmdGlobalConstants;
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



    public static function guardTooManyOrdersToBeCreated($commandData)
    {
        $ithDayOfTheYearForStartDate = getdate(strtotime($commandData['dateFrom']))['yday'];
        $ithDayOfTheYearForEndDate = getdate(strtotime($commandData['dateTo']))['yday'];

        $periodDays = $ithDayOfTheYearForEndDate - $ithDayOfTheYearForStartDate;        
        $max = $commandData['maxBaseNumOfDailyOrders'];

        if (($periodDays * $max) > BmdGlobalConstants::MAX_NUM_OF_FAKE_ORDERS_TO_BE_GENERATED_PER_SCHEDULED_TASK) {
            $exceptionMsg = 'BMD Exception: ' . BmdExceptions::MAX_NUM_OF_FAKE_ORDERS_TO_BE_GENERATED_PER_SCHEDULED_TASK['id'];
            $exceptionMsg .= ' => ' . BmdExceptions::MAX_NUM_OF_FAKE_ORDERS_TO_BE_GENERATED_PER_SCHEDULED_TASK['name'];
            throw new Exception($exceptionMsg);
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
