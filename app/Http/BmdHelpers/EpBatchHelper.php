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
}
