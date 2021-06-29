<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduledTaskLog;
use App\Models\ScheduledTaskStatus;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;

class ScheduledTaskLogController extends Controller
{
    public function read(Request $r) {

        Gate::forUser(BmdAuthProvider::user())->authorize('generalProcess', ScheduledTask::class);
        
        
        $logs = ScheduledTaskLog::where('scheduled_task_id', $r->jobId)->orderBy('created_at', 'desc')->take(20)->get();

        foreach ($logs as $l) {
            $l->status_readable_name = ScheduledTaskStatus::where('code', $l->status_code)->get()[0]->readable_name;
        }

        return [
            'isResultOk' => true,
            'objs' => [
                'logs' => $logs
            ]
        ];
    }
}
