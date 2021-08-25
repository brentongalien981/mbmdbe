<?php

namespace App\Http\Controllers;

use App\Bmd\Generals\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Models\PurchaseItemStatus;
use App\Models\PurchaseStatus;
use Exception;

class PurchaseController extends Controller
{
    private const NUM_OF_DISPLAYED_PURCHASES_PER_PAGE = 10;



    public function index(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Purchase::class);


        $purchasesQuery = $this->getPurchasesQuery($r);
        $totalNumOfPurchasesForQuery = $purchasesQuery->count();
        
        $numOfPurchasesToSkip = ($r->pageNum - 1) * self::NUM_OF_DISPLAYED_PURCHASES_PER_PAGE;

        $purchases = $purchasesQuery->skip($numOfPurchasesToSkip)
            ->take(self::NUM_OF_DISPLAYED_PURCHASES_PER_PAGE)
            ->get();

        $purchases = PurchaseResource::collection($purchases);

        
        return [
            'isResultOk' => true,
            'objs' => [
                'purchases' => $purchases,
                'paginationData' => [
                    'totalNumOfPurchasesForQuery' => $totalNumOfPurchasesForQuery
                ]
            ]
        ];
    }



    private function getPurchasesQuery(Request $r)
    {
        $idQueryPhrase = '%' . $r->id . '%';
        $sellerIdQueryPhrase = '%' . $r->sellerId . '%';

        $chargedSubtotalQueryPhrase = '%' . $r->chargedSubtotal . '%';
        $chargedShippingFeeQueryPhrase = '%' . $r->chargedShippingFee . '%';
        $chargedOtherFeeQueryPhrase = '%' . $r->chargedOtherFee . '%';
        $chargedTaxQueryPhrase = '%' . $r->chargedTax . '%';

        $statusCodeQueryPhrase = '%' . $r->statusCode . '%';
        $orderIdFromSellerSiteQueryPhrase = '%' . $r->orderIdFromSellerSite . '%';
        $shippingIdFromCarrierQueryPhrase = '%' . $r->shippingIdFromCarrier . '%';
        $notesQueryPhrase = '%' . $r->notes . '%';


        $purchasesQuery = Purchase::where('id', 'like', $idQueryPhrase)
            ->where('seller_id', 'like', $sellerIdQueryPhrase)
            ->where('status_code', 'like', $statusCodeQueryPhrase);

        if (trim($r->chargedSubtotal) != '') {
            $purchasesQuery = $purchasesQuery->where('charged_subtotal', 'like', $chargedSubtotalQueryPhrase);
        }
        if (trim($r->chargedShippingFee) != '') {
            $purchasesQuery = $purchasesQuery->where('charged_shipping_fee', 'like', $chargedShippingFeeQueryPhrase);
        }
        if (trim($r->chargedOtherFee) != '') {
            $purchasesQuery = $purchasesQuery->where('charged_other_fee', 'like', $chargedOtherFeeQueryPhrase);
        }
        if (trim($r->chargedTax) != '') {
            $purchasesQuery = $purchasesQuery->where('charged_tax', 'like', $chargedTaxQueryPhrase);
        }
        if (trim($r->orderIdFromSellerSite) != '') {
            $purchasesQuery = $purchasesQuery->where('order_id_from_seller_site', 'like', $orderIdFromSellerSiteQueryPhrase);
        }
        if (trim($r->shippingIdFromCarrier) != '') {
            $purchasesQuery = $purchasesQuery->where('shipping_id_from_carrier', 'like', $shippingIdFromCarrierQueryPhrase);
        }
        if (trim($r->notes) != '') {
            $purchasesQuery = $purchasesQuery->where('notes', 'like', $notesQueryPhrase);
        }


        $purchasesQuery = $purchasesQuery
            ->where('created_at', '>=', $r->createdAt)
            ->where('updated_at', '>=', $r->updatedAt)

            ->orderBy('created_at', 'desc');

        return $purchasesQuery;
    }



    public function show(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Purchase::class);


        $v = $r->validate([
            'purchaseId' => 'required|integer',
        ]);


        $p = Purchase::find($v['purchaseId']);


        return [
            'isResultOk' => true,
            'objs' => [
                'purchase' => new PurchaseResource($p) ?? [],
                'purchaseStatuses' => PurchaseStatus::orderBy('name', 'asc')->get(),
                'purchaseItemStatuses' => PurchaseItemStatus::orderBy('name', 'asc')->get()
            ]
        ];
    }



    
    public function store(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Purchase::class);

        
        $v = $this->validateRequestData($r, 'create');
        $p = Purchase::saveWithData($v, 'create');
        

        return [
            'isResultOk' => true,
            'objs' => [
                'purchase' => new PurchaseResource($p)
            ]
        ];
    }



    private function validateRequestData(Request $r, $crudAction = 'create')
    {
        $idValidationRule = ($crudAction === 'create' ? 'nullable|integer' : 'required|integer');

        return $r->validate([
            'id' => $idValidationRule,
            'sellerId' => 'required|integer|exists:sellers,id',
            'projectedSubtotal' => 'nullable|numeric',
            'projectedShippingFee' => 'nullable|numeric',
            'projectedOtherFee' => 'nullable|numeric',
            'projectedTax' => 'nullable|numeric',
            'chargedSubtotal' => 'nullable|numeric',
            'chargedShippingFee' => 'nullable|numeric',
            'chargedOtherFee' => 'nullable|numeric',
            'chargedTax' => 'nullable|numeric',
            'statusCode' => 'required|integer',
            'estimatedDeliveryDate' => 'nullable|date',
            'orderIdFromSellerSite' => 'nullable|string|max:128',
            'shippingIdFromCarrier' => 'nullable|string|max:128',
            'notes' => 'nullable|string|max:1024'
        ]);
    }
}
