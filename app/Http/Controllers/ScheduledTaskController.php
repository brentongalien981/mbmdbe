<?php

namespace App\Http\Controllers;

use App\Models\ScheduledTask;
use Illuminate\Http\Request;

class ScheduledTaskController extends Controller
{
    public function index(Request $r)
    {
        
        return [
            'isResultOk' => true,
            'objs' => [
                'automatedJobs' => ScheduledTask::all()
            ]
        ];
    }
}
