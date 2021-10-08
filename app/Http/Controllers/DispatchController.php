<?php

namespace App\Http\Controllers;

use Exception;
use EasyPost\Batch;
use EasyPost\Pickup;
use App\Models\Order;
use EasyPost\EasyPost;
use EasyPost\Shipment;
use App\Models\Dispatch;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use App\Models\DispatchStatus;
use App\Bmd\Generals\GeneralHelper2;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\OrderResource;
use App\Http\BmdHelpers\EpBatchHelper;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\Resources\DispatchResource;
use App\Http\BmdHttpResponseCodes\GeneralHttpResponseCodes;

class DispatchController extends Controller
{

    private const NUM_OF_DISPLAYED_DISPATCHES_PER_PAGE = 10;



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



    public function update(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Dispatch::class);

        $isResultOk = false;
        $resultCode = null;
        $dispatch = null;


        try {
            $dispatch = Dispatch::find($r->dispatchId);
            if (!$dispatch) {
                throw new Exception('Dispatch Not Found.');
            }
    
    
            $statusCode = DispatchStatus::where('code', $r->dispatchStatusCode)->get()[0]->code;    
            if (!$statusCode) {
                throw new Exception('Status Code Not Found.');
            }
            
            
            $dispatch->status_code = $statusCode;
            $dispatch->save();
            $dispatch = new DispatchResource($dispatch);

            $isResultOk = true;
        } catch (\Throwable $th) {
            $resultCode = GeneralHttpResponseCodes::getGeneralExceptionCode($th);
        }


        return [
            'isResultOk' => $isResultOk,
            'objs' => [
                'dispatch' => $dispatch
            ],
            'resultCode' => $resultCode

        ];
    }



    public function index(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Dispatch::class);


        $dispatchesQuery = $this->getDispatchesQuery($r);
        $totalNumOfDispatchesForQuery = $dispatchesQuery->count();

        $numOfDispatchesToSkip = ($r->pageNum - 1) * self::NUM_OF_DISPLAYED_DISPATCHES_PER_PAGE;

        $dispatches = $dispatchesQuery->skip($numOfDispatchesToSkip)
            ->take(self::NUM_OF_DISPLAYED_DISPATCHES_PER_PAGE)
            ->get();

        $dispatches = DispatchResource::collection($dispatches);


        return [
            'isResultOk' => true,
            'objs' => [
                'dispatches' => $dispatches,
                'paginationData' => [
                    'totalNumOfDispatchesForQuery' => $totalNumOfDispatchesForQuery
                ]
            ]
        ];
    }



    private function getDispatchesQuery(Request $r)
    {
        $idQueryPhrase = '%' . $r->id . '%';
        $epBatchIdQueryPhrase = '%' . $r->epBatchId . '%';
        $statusCodeQueryPhrase = '%' . $r->statusCode . '%';
        $notesQueryPhrase = '%' . $r->notes . '%';


        $dispatchesQuery = Dispatch::where('id', 'like', $idQueryPhrase)
            ->where('ep_batch_id', 'like', $epBatchIdQueryPhrase)
            ->where('status_code', 'like', $statusCodeQueryPhrase);


        if (trim($r->notes) != '') {
            $dispatchesQuery = $dispatchesQuery->where('notes', 'like', $notesQueryPhrase);
        }


        $dispatchesQuery = $dispatchesQuery
            ->where('created_at', '>=', $r->createdAt)
            ->where('updated_at', '>=', $r->updatedAt)

            ->orderBy('created_at', 'desc');

        return $dispatchesQuery;
    }



    public function addOrderToDispatch(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Dispatch::class);

        $isResultOk = false;
        $order = null;
        $dispatch = null;
        $resultCode = null;


        try {
            GeneralHelper2::setEasyPostApiKey();

            $order = Order::findOrFail($r->orderId);
            $dispatch = Dispatch::findOrFail($r->dispatchId);

            EpBatchHelper::addShipmentToBatch($order, $dispatch);

            $order->dispatch_id = $dispatch->id;
            $order->status_code = OrderStatus::getCodeByName('TO_BE_DISPATCHED');
            $order->save();

            $isResultOk = true;
        } catch (\Throwable $th) {
            $resultCode = GeneralHttpResponseCodes::getGeneralExceptionCode($th);
        }


        return [
            'isResultOk' => $isResultOk,
            'objs' => [
                'order' => new OrderResource($order)
            ],
            'resultCode' => $resultCode
        ];
    }



    public function removeOrderFromDispatch(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Dispatch::class);

        $isResultOk = false;
        $order = null;
        $dispatch = null;
        $epBatch = null;
        $resultCode = null;


        try {
            GeneralHelper2::setEasyPostApiKey();

            $order = Order::findOrFail($r->orderId);
            $dispatch = Dispatch::findOrFail($r->dispatchId);


            // Validate that order belongs to dispatch.            
            if ($order->dispatch_id != $dispatch->id) {
                throw new Exception('Order has wrong dispatch id.');
            }


            // Validate that ep-batch has ep-shipment-id equal to order's ep-shipment-id.
            $epBatch = Batch::retrieve($dispatch->ep_batch_id);
            if (!EpBatchHelper::doesBatchHaveShipmentWithId($epBatch, $order->ep_shipment_id)) {
                throw new Exception('EP-Batch does not have that EP-Shipment.');
            }


            // Remove ep-shipment from ep-batch.
            EpBatchHelper::removeShipmentFromBatch($epBatch, $order->ep_shipment_id);


            // Set order's dispatch-id to null.
            $order->dispatch_id = null;
            $order->save();

            $isResultOk = true;
        } catch (\Throwable $th) {
            $resultCode = GeneralHttpResponseCodes::getGeneralExceptionCode($th);
        }


        return [
            'isResultOk' => $isResultOk,
            'objs' => [
                'dispatch' => new DispatchResource($dispatch),
                'dispatchOrders' => OrderResource::collection($dispatch->orders),
                'epBatch' => GeneralHelper2::pseudoJsonify($epBatch)
            ],
            'resultCode' => $resultCode
        ];
    }



    public function show(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Dispatch::class);

        $isResultOk = false;
        $dispatch = null;
        $epBatch = null;
        $dispatchOrders = [];
        $resultCode = null;


        try {
            GeneralHelper2::setEasyPostApiKey();

            $dispatch = Dispatch::findOrFail($r->dispatchId);
            $dispatch = new DispatchResource($dispatch);

            $epBatch = Batch::retrieve($dispatch->ep_batch_id);
            $epBatch = GeneralHelper2::pseudoJsonify($epBatch);

            $dispatchOrders = OrderResource::collection($dispatch->orders);

            $isResultOk = true;
        } catch (\Throwable $th) {
            $resultCode = GeneralHttpResponseCodes::getGeneralExceptionCode($th);
        }


        return [
            'isResultOk' => $isResultOk,
            'objs' => [
                'dispatch' => $dispatch,
                'dispatchStatuses' => DispatchStatus::orderBy('name', 'asc')->get(),
                'dispatchOrders' => $dispatchOrders,
                'epBatch' => $epBatch
            ],
            'resultCode' => $resultCode
        ];
    }



    public function saveEpBatchPickupInfo(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Dispatch::class);

        $isResultOk = false;
        $aDispatchOrder = null;
        $anEpShipmentAddress = null;
        $dispatch = null;
        $epBatch = null;
        $epPickup = null;
        $resultCode = null;


        try {
            GeneralHelper2::setEasyPostApiKey();


            // Reference dispatch, epBatch
            $dispatch = Dispatch::findOrFail($r->dispatchId);
            $epBatch = Batch::retrieve($dispatch->ep_batch_id);

            if ($epBatch->pickup) {
                throw new Exception('EP-Batch already has pickup.');
            }


            // Reference the epPickup's address.
            $aDispatchOrder = $dispatch->orders[0] ?? null;
            if (!$aDispatchOrder) {
                throw new Exception('This dispatch has no orders.');
            }
            $anEpShipmentAddress = Shipment::retrieve($aDispatchOrder->ep_shipment_id)->from_address;
            $aDispatchOrder = new OrderResource($aDispatchOrder) ?? null;


            // Set epPickup
            $epPickup = Pickup::create([
                'address' => $anEpShipmentAddress,
                'batch' => $epBatch,
                'reference' => $r->referenceString,
                'min_datetime' => date('Y-m-d H:i:s T', strtotime($r->epBatchEarliestPickupDatetime)),
                'max_datetime' => date('Y-m-d H:i:s T', strtotime($r->epBatchLatestPickupDatetime)),
                'is_account_address' => false,
                'instructions' => $r->carrierNotes
            ]);


            $epBatch = Batch::retrieve($dispatch->ep_batch_id);


            $isResultOk = true;
        } catch (\Throwable $th) {
            $resultCode = GeneralHttpResponseCodes::getGeneralExceptionCode($th);
        }


        return [
            'isResultOk' => $isResultOk,
            'objs' => [
                'aDispatchOrder' => $aDispatchOrder,
                'dispatch' => new DispatchResource($dispatch),
                'anEpShipmentAddress' => GeneralHelper2::pseudoJsonify($anEpShipmentAddress),
                'epBatch' => GeneralHelper2::pseudoJsonify($epBatch),
                'epPickup' => GeneralHelper2::pseudoJsonify($epPickup)
            ],
            'resultCode' => $resultCode
        ];
    }



    public function buyPickupRate(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Dispatch::class);

        $isResultOk = false;
        $dispatch = null;
        $epBatch = null;
        $epPickup = null;
        $epPickupRate = null;
        $resultCode = null;


        try {
            GeneralHelper2::setEasyPostApiKey();


            $dispatch = Dispatch::find($r->dispatchId);
            $epBatch = Batch::retrieve($dispatch->ep_batch_id);
            $epPickup = Pickup::retrieve($r->epPickupId);


            EpBatchHelper::validateObjsForBuyingPickup($dispatch, $epBatch, $epPickup);
            $epPickupRate = EpBatchHelper::buyPickupRate($epPickup, $r->epPickupRateId);


            // update dispatch
            $newDispatchStatusCode = DispatchStatus::where('name', 'EP_PICKUP_BOUGHT')->get()[0]->code;
            $dispatch->status_code = $newDispatchStatusCode;
            $dispatch->pickup_cost = $epPickupRate->rate;
            $dispatch->save();


            $isResultOk = true;


            // Re-reference the updated objs.
            $dispatch = new DispatchResource($dispatch);
            $epBatch = Batch::retrieve($dispatch->ep_batch_id);
            $epBatch = GeneralHelper2::pseudoJsonify($epBatch);
            $epPickupRate = GeneralHelper2::pseudoJsonify($epPickupRate);
        } catch (\Throwable $th) {
            $resultCode = GeneralHttpResponseCodes::getGeneralExceptionCode($th);
        }


        return [
            'isResultOk' => $isResultOk,
            'objs' => [
                'dispatch' => $dispatch,
                'epBatch' => $epBatch,
                'epPickupRate' => $epPickupRate
            ],
            'resultCode' => $resultCode
        ];
    }



    public function cancelPickup(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Dispatch::class);

        $isResultOk = false;
        $dispatch = null;
        $epBatch = null;
        $resultCode = null;


        try {
            GeneralHelper2::setEasyPostApiKey();

            $dispatch = Dispatch::find($r->dispatchId);
            $epBatch = Batch::retrieve($dispatch->ep_batch_id);
            $epPickup = Pickup::retrieve($r->epPickupId);


            EpBatchHelper::validateObjsForCancellingPickup($dispatch, $epBatch, $epPickup);

            $epPickup->cancel();

            // update dispatch
            $newDispatchStatusCode = DispatchStatus::where('name', 'EP_BATCH_UPDATED')->get()[0]->code;
            $dispatch->status_code = $newDispatchStatusCode;
            $dispatch->pickup_cost = null;
            $dispatch->save();


            $isResultOk = true;


            // Re-reference the updated objs.
            $dispatch = new DispatchResource($dispatch);
            $epBatch = Batch::retrieve($dispatch->ep_batch_id);
            $epBatch = GeneralHelper2::pseudoJsonify($epBatch);
        } catch (\Throwable $th) {
            $resultCode = GeneralHttpResponseCodes::getGeneralExceptionCode($th);
        }


        return [
            'isResultOk' => $isResultOk,
            'objs' => [
                'dispatch' => $dispatch,
                'epBatch' => $epBatch
            ],
            'resultCode' => $resultCode
        ];
    }
}
