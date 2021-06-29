<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\ScheduledTask;
use App\Models\ScheduledTaskLog;
use App\Models\ScheduledTaskStatus;
use App\Events\PrepareBmdPurchasesCommandEvent;
use App\Events\SyncBmdSellerProductsWithInventoryEvent;

class ScheduledTaskController extends Controller
{
    public const RESULT_CODE_COMMAND_UNAVAILABLE = -1;
    public const RESULT_CODE_COMMAND_DOES_NOT_EXIST = -2;

    public const RESULT_CODE_COMMAND_EXECUTED = 1;



    public function resetJobStatus(Request $r)
    {
        $aj = ScheduledTask::find($r->jobId);
        $aj->status_code = ScheduledTaskStatus::where('name', 'AVAILABLE')->get()[0]->code;
        $aj->save();


        return [
            'isResultOk' => true,
            'objs' => [
                'jobId' => $r->jobId
            ]
        ];
    }



    public function index(Request $r)
    {

        $aj = ScheduledTask::all();
        foreach ($aj as $j) {
            $j->status = ScheduledTaskStatus::where('code', $j->status_code)->get()[0];
            $j->last_log = ScheduledTaskLog::where('scheduled_task_id', $j->id)->orderBy('created_at', 'desc')->get()[0] ?? null;
        }

        return [
            'isResultOk' => true,
            'objs' => [
                'automatedJobs' => $aj
            ]
        ];
    }



    public function execute(Request $r)
    {
        $overallProcessLogs = [];
        $isResultOk = false;
        $resultCode = 0;

        $scheduledTask = ScheduledTask::find($r->jobId)->get()[0];
        $availableStatus = ScheduledTaskStatus::where('name', 'AVAILABLE')->get()[0];

        $commandData = [
            'jobId' => $r->jobId,
            'dateFrom' => $r->dateFrom,
            'dateTo' => $r->dateTo
        ];


        if ($scheduledTask->status_code == $availableStatus->code) {
            switch ($r->jobId) {
                case ScheduledTask::where('command_signature', 'SyncBmdSellerProductsWithInventory:Execute')->get()[0]->id:
                    SyncBmdSellerProductsWithInventoryEvent::dispatch($commandData);
                    $isResultOk = true;
                    $resultCode = self::RESULT_CODE_COMMAND_EXECUTED;
                    break;

                case ScheduledTask::where('command_signature', 'BmdPurchases:Prepare')->get()[0]->id:
                    PrepareBmdPurchasesCommandEvent::dispatch($commandData);
                    $isResultOk = true;
                    $resultCode = self::RESULT_CODE_COMMAND_EXECUTED;
                    break;

                default:
                    $resultCode = self::RESULT_CODE_COMMAND_DOES_NOT_EXIST;
                    break;
            }
        } else {
            $resultCode = self::RESULT_CODE_COMMAND_UNAVAILABLE;
        }



        return [
            'isResultOk' => $isResultOk,
            'resultCode' => $resultCode,
            'objs' => [
                'overallProcessLogs' => $overallProcessLogs, // BMD-ON-STAGING
            ]
        ];
    }
}
