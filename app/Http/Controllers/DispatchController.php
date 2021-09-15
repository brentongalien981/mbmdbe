<?php

namespace App\Http\Controllers;

use App\Bmd\Generals\GeneralHelper2;
use EasyPost\Batch;
use EasyPost\EasyPost;
use App\Models\Dispatch;
use Illuminate\Http\Request;
use App\Models\DispatchStatus;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;
use Exception;

class DispatchController extends Controller
{
    public function store(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Dispatch::class);

        // BMD-ON-ITER: Staging
        EasyPost::setApiKey(env('EASYPOST_TK'));
        
        $batch = Batch::create();


        $dispatch = new Dispatch();
        $dispatch->ep_batch_id = $batch->id;
        $dispatch->status_code = DispatchStatus::where('name', 'EP_BATCH_CREATING')->get()[0]->code;
        $dispatch->save();

        
        return [
            'isResultOk' => true,
            'objs' => [
                'dispatch' => $dispatch,
                'epBatch' => GeneralHelper2::pseudoJsonify($batch)
            ]
        ];
    }
}
