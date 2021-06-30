<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\ScheduledTask;
use App\Models\ScheduledTaskLog;
use App\Models\ScheduledTaskStatus;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Events\PrepareBmdPurchasesCommandEvent;
use App\Events\ResetSizeAvailabilityQuantitiesOfNonBmdSellerProductsEvent;
use App\Events\SyncBmdSellerProductsWithInventoryEvent;

class ScheduledTaskController extends Controller
{
    public const RESULT_CODE_COMMAND_UNAVAILABLE = -1;
    public const RESULT_CODE_COMMAND_DOES_NOT_EXIST = -2;

    public const RESULT_CODE_COMMAND_EXECUTED = 1;



    public function resetJobStatus(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('generalProcess', ScheduledTask::class);


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

        // Authorize.
        Gate::forUser(BmdAuthProvider::user())->authorize('viewAny', ScheduledTask::class);


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
        Gate::forUser(BmdAuthProvider::user())->authorize('execute', ScheduledTask::class);


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

        $event = null;


        if ($scheduledTask->status_code == $availableStatus->code) {
            switch ($r->jobId) {
                case ScheduledTask::where('command_signature', 'ResetSizeAvailabilityQuantitiesOfNonBmdSellerProducts:Execute')->get()[0]->id:
                    $event = ResetSizeAvailabilityQuantitiesOfNonBmdSellerProductsEvent::class;
                    break;
                case ScheduledTask::where('command_signature', 'SyncBmdSellerProductsWithInventory:Execute')->get()[0]->id:
                    $event = SyncBmdSellerProductsWithInventoryEvent::class;
                    break;
                case ScheduledTask::where('command_signature', 'BmdPurchases:Prepare')->get()[0]->id:
                    $event = PrepareBmdPurchasesCommandEvent::class;
                    break;
                default:
                    $resultCode = self::RESULT_CODE_COMMAND_DOES_NOT_EXIST;
                    break;
            }
        } else {
            $resultCode = self::RESULT_CODE_COMMAND_UNAVAILABLE;
        }


        if ($event) {
            $event::dispatch($commandData);
            $isResultOk = true;
            $resultCode = self::RESULT_CODE_COMMAND_EXECUTED;
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
