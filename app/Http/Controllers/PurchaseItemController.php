<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Rules\PurchaseItemSellerIdEqualsPurchaseSellerId;
use App\Rules\SizeAvailabilityBelongsToSellerProduct;

class PurchaseItemController extends Controller
{
    public function store(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Purchase::class);

        $v = $this->validateRequestData($r, 'store');

        $extraValidationData = [
            'purchaseId' => $v['purchaseId'],
            'sellerProductId' => $v['sellerProductId'],
            'sizeAvailabilityId' => $v['sizeAvailabilityId']
        ];

        if (
            PurchaseItemSellerIdEqualsPurchaseSellerId::bmdValidate($extraValidationData)
            && SizeAvailabilityBelongsToSellerProduct::bmdValidate($extraValidationData)
        ) {
            // $p = Purchase::saveWithData($v, 'update');
            // $p = new PurchaseResource($p);
        } else {
            // BMD-TODO
            // $isResultOk = false;
            // $resultCode = PurchaseHttpResponseCodes::PURCHASE_SELLER_SHOULD_EQUAL_PURCHASE_ITEMS_SELLERS;
        }


        // $savedPurchaseItem = $this->saveWithData($v, 'store');

        return [
            'isResultOk' => true,
            'objs' => [
                // 'savedPurchaseItem' => new OrderItemResource($savedPurchaseItem)
            ]
        ];
    }



    private function validateRequestData(Request $r, $crudAction = 'store')
    {
        $idValidationRule = ($crudAction === 'store' ? 'nullable' : 'required|integer');

        return $r->validate([
            'id' => $idValidationRule,
            'purchaseId' => 'required|exists:purchase,id',
            'sellerProductId' => 'required|exists:product_seller,id',
            'sizeAvailabilityId' => 'required|exists:size_availabilities,id',
            'quantity' => 'required|integer',
            'projectedPrice' => 'required|numeric',
            'actualPrice' => 'nullable|numeric',
            'statusCode' => 'required|exists:purchase_item_status,code'
        ]);
    }
}
