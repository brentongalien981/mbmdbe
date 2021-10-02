<?php

namespace App\Http\Controllers;

use Exception;
use EasyPost\Batch;
use App\Models\Order;
use EasyPost\EasyPost;
use App\Models\Dispatch;
use Illuminate\Http\Request;
use App\Models\DispatchStatus;
use App\Bmd\Generals\GeneralHelper2;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\BmdHelpers\EpBatchHelper;
use App\Http\Resources\DispatchResource;
use App\Http\BmdHttpResponseCodes\GeneralHttpResponseCodes;
use App\Http\Resources\OrderResource;
use App\Models\OrderStatus;

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



    public function show(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Dispatch::class);


        $isResultOk = false;
        $dispatch = null;
        $epBatch = null;
        $resultCode = null;


        try {
            GeneralHelper2::setEasyPostApiKey();

            $dispatch = Dispatch::findOrFail($r->dispatchId);
            $dispatch = new DispatchResource($dispatch);

            $epBatch = Batch::retrieve($dispatch->ep_batch_id);
            $epBatch = GeneralHelper2::pseudoJsonify($epBatch);

            $isResultOk = true;
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
