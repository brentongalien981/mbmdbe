<?php

namespace App\Http\BmdHelpers;

use Exception;
use EasyPost\Batch;
use App\Models\DispatchStatus;

class EpBatchHelper
{
    public static function addShipmentToBatch($order, $dispatch)
    {
        if ($order->dispatch_id) {
            throw new Exception('Order already belongs to a dispatch.');
        }

        if (!$dispatch->ep_batch_id) {
            throw new Exception('Dispatch does not have EP-Batch-ID.');
        }


        $allowedDispatchStatusCodes = DispatchStatus::whereIn('name', ['EP_BATCH_CREATED', 'EP_BATCH_UPDATED'])->get()->pluck('code');
        $codes = [];
        foreach ($allowedDispatchStatusCodes as $code) {
            $codes[] = $code;
        }
        if (!in_array($dispatch->status_code, $codes)) {
            throw new Exception('Dispatch-status not allowed to add order.');
        }


        // SAMPLE
        // $batchingShipmentsParams = ['shipments' => [
        //     ['id' => 'shp_...'],
        //     ['id' => 'shp_...']
        // ]];

        $batchingShipmentsParams[] = ['id' => $order->ep_shipment_id];


        $epBatch = Batch::retrieve($dispatch->ep_batch_id);
        $epBatch->add_shipments(['shipments' => $batchingShipmentsParams]);
    }



    public static function doesBatchHaveShipmentWithId($epBatch, $epShipmentId)
    {
        foreach ($epBatch->shipments as $shipment) {
            if ($shipment->id == $epShipmentId) {
                return true;
            }
        }

        return false;
    }


    
    public static function removeShipmentFromBatch($epBatch, $epShipmentId)
    {

        $batchingShipmentsParamsToRemove = [];
        $batchingShipmentsParamsToRemove[] = ['id' => $epShipmentId];

        $epBatch->remove_shipments(['shipments' => $batchingShipmentsParamsToRemove]);

    }



    public static function buyPickupRate($epPickup, $epPickupRateId)
    {

        $selectedPickupRate = null;
        
        foreach ($epPickup->pickup_rates as $r) {
            if ($r->id === $epPickupRateId) {
                $selectedPickupRate = $r;
            }
        }

        if (!$selectedPickupRate) { throw new Exception('No matching EP-Pickup Rate found.'); }

        $epPickup->buy([
            'carrier' => $selectedPickupRate->carrier,
            'service' => $selectedPickupRate->service
        ]);

        
        return $selectedPickupRate;
    }



    public static function validateObjsForBuyingPickup($dispatch, $epBatch, $epPickup)
    {
        // check if dispatch, ep-batch, ep-pickup are all related
        if (!$dispatch) { throw new Exception('Dispatch does not exist.'); }
        if (!$epBatch) { throw new Exception('Invalid EP-Batch'); }
        if (!$epPickup) { throw new Exception('Invalid EP-Pickup'); }
        if (!$epBatch->pickup || $epBatch->pickup->id !== $epPickup->id) { throw new Exception('Invalid EP-Pickup'); }


        // check if ep-pickup hasn’t been bought yet
        if ($epPickup->status === 'scheduled' || $epPickup->confirmation) { throw new Exception('EP-Pickup has already been bought.'); }
    }



    public static function validateObjsForCancellingPickup($dispatch, $epBatch, $epPickup)
    {
        // check if dispatch, ep-batch, ep-pickup are all related
        if (!$dispatch) { throw new Exception('Dispatch does not exist.'); }
        if (!$epBatch) { throw new Exception('Invalid EP-Batch'); }
        if (!$epPickup) { throw new Exception('Invalid EP-Pickup'); }
        if (!$epBatch->pickup || $epBatch->pickup->id !== $epPickup->id) { throw new Exception('Invalid EP-Pickup'); }
    }
}
