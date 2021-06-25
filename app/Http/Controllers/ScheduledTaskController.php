<?php

namespace App\Http\Controllers;

use App\Models\ScheduledTask;
use App\Models\ScheduledTaskLog;
use App\Models\ScheduledTaskStatus;
use Illuminate\Http\Request;

class ScheduledTaskController extends Controller
{
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



    public function execute(Request $r) {
        
        return [
            'msg' => 'In METHOD: execute()',
            'r->jobId' => $r->jobId,
            'r->dateFrom' => $r->dateFrom,
            'r->dateTo' => $r->dateTo
        ];
    }
}
