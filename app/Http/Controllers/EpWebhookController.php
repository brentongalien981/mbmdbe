<?php

namespace App\Http\Controllers;

use App\Models\EpEvent;
use Illuminate\Http\Request;
use App\Bmd\Generals\GeneralHelper2;
use Illuminate\Support\Facades\Mail;
use App\Bmd\Constants\BmdGlobalConstants;
use App\Jobs\EmailEpBatchUpdate;
use App\Mail\EpBatchUpdated;
use App\Models\Dispatch;
use App\Models\DispatchStatus;
use Exception;

class EpWebhookController extends Controller
{
    public function receiveEvent(Request $r)
    {
        if (!isset($r->key) || $r->key != env('BMD_EASYPOST_WEBHOOK_KEY')) {
            return response('Unauthorized', 401);
        }


        $pseudoJsonifiedR = GeneralHelper2::pseudoJsonify($r->request);

        $eventDescription = $pseudoJsonifiedR['description'];


        switch ($eventDescription) {
            case BmdGlobalConstants::EP_EVENT_DESCRIPTIONS['BATCH_CREATED']:
                
                // Save ep-event.
                $eventId = $pseudoJsonifiedR['id'];

                $bmdEpEvent = new EpEvent();
                $bmdEpEvent->ep_event_id = $eventId;
                $bmdEpEvent->event_description = $eventDescription;
                $bmdEpEvent->event_json = json_encode($pseudoJsonifiedR);
                $bmdEpEvent->save();
    
    
                // Update dispatch status.
                $epBatchId = $pseudoJsonifiedR['result']['id'];
                $newStatus = DispatchStatus::where('name', 'EP_BATCH_CREATED')->get()[0];
    
                $dispatch = Dispatch::where('ep_batch_id', $epBatchId)->get()[0];                
                $dispatch->status_code = $newStatus->code;
                $dispatch->save();
    

                // Send ep-batch email notification.            
                if (isset($dispatch)) {
                    EmailEpBatchUpdate::dispatch($dispatch->id)->onQueue(BmdGlobalConstants::QUEUE_FOR_EP_WEBHOOKS);
                }

                
                break;
            case BmdGlobalConstants::EP_EVENT_DESCRIPTIONS['BATCH_UPDATED']:
                break;
            default:
                break;
        }


        return ['response to EP with status 200'];
    }



    public function receiveProductionEvent(Request $r)
    {
        if (!isset($r->key) || $r->key != env('BMD_EASYPOST_WEBHOOK_KEY')) {
            return response('Unauthorized', 401);
        }


        $pseudoJsonifiedR = GeneralHelper2::pseudoJsonify($r->request);

        $eventDescription = $pseudoJsonifiedR['description'];


        switch ($eventDescription) {
            case BmdGlobalConstants::EP_EVENT_DESCRIPTIONS['BATCH_CREATED']:
                
                // Save ep-event.
                $eventId = $pseudoJsonifiedR['id'];

                $bmdEpEvent = new EpEvent();
                $bmdEpEvent->ep_event_id = $eventId;
                $bmdEpEvent->event_description = $eventDescription;
                $bmdEpEvent->event_json = json_encode($pseudoJsonifiedR);
                $bmdEpEvent->save();
    
    
                // Update dispatch status.
                $epBatchId = $pseudoJsonifiedR['result']['id'];
                $newStatus = DispatchStatus::where('name', 'EP_BATCH_CREATED')->get()[0];
    
                $dispatch = Dispatch::where('ep_batch_id', $epBatchId)->get()[0];                
                $dispatch->status_code = $newStatus->code;
                $dispatch->save();
    

                // Send ep-batch email notification.            
                if (isset($dispatch)) {
                    EmailEpBatchUpdate::dispatch($dispatch->id)->onQueue(BmdGlobalConstants::QUEUE_FOR_EP_WEBHOOKS);
                }

                
                break;
            case BmdGlobalConstants::EP_EVENT_DESCRIPTIONS['BATCH_UPDATED']:
                break;
            default:
                break;
        }


        return ['response to EP with status 200'];
    }    
}
