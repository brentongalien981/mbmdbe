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
use App\Http\Resources\DispatchResource;
use Exception;

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
}
