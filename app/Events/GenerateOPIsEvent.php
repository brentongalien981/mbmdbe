<?php

namespace App\Events;

use App\Bmd\Constants\BmdGlobalConstants;
use Exception;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class GenerateOPIsEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
            throw new Exception('MAX_NUM_OF_FAKE_ORDERS_TO_BE_GENERATED_PER_SCHEDULED_TASK Reached!');
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
