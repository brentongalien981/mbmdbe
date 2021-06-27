<?php

namespace App\Http\Controllers;

use App\Models\ScheduledTaskLog;
use Illuminate\Http\Request;

class ScheduledTaskLogController extends Controller
{
    public function read(Request $r) {

        return [
            'isResultOk' => true,
            'objs' => [
                'logs' => ScheduledTaskLog::where('scheduled_task_id', $r->jobId)->take(10)->get()
            ]
        ];
    }
}
