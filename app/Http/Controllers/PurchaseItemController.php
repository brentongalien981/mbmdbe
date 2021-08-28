<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\OrderItem;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Rules\SizeAvailabilityBelongsToSellerProduct;
use App\Rules\PurchaseItemSellerIdEqualsPurchaseSellerId;
use App\Http\BmdHttpResponseCodes\PurchaseItemHttpResponseCodes;
use App\Http\Resources\PurchaseItemResource;

class PurchaseItemController extends Controller
{
    public function store(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Purchase::class);

        $v = $this->validateRequestData($r, 'store');

        $isResultOk = true;
        $resultCode = null;

        $extraValidationData = [
            'purchaseId' => $v['purchaseId'],
            'sellerProductId' => $v['sellerProductId'],
            'sizeAvailabilityId' => $v['sizeAvailabilityId']
        ];


        try {
            if (!PurchaseItemSellerIdEqualsPurchaseSellerId::bmdValidate($extraValidationData)) {
                $resultCode = PurchaseItemHttpResponseCodes::PURCHASE_ITEM_SELLER_SHOULD_EQUAL_PURCHASE_SELLER;
                throw new Exception();
            }

            if (!SizeAvailabilityBelongsToSellerProduct::bmdValidate($extraValidationData)) {
                $resultCode = PurchaseItemHttpResponseCodes::SIZE_AVAILABILITY_DOES_NOT_BELONG_TO_SELLER_PRODUCT;
                throw new Exception();
            }
        } catch (Exception $e) {
            $isResultOk = false;
        }


        if ($isResultOk) {

            // Db transaction
            DB::beginTransaction();


            // Save purchase-item.
            $savedPurchaseItem = PurchaseItem::saveWithData($v, 'store');


            // BMD-DELETE
            $oldOrderItems = OrderItem::where('purchase_item_id', $savedPurchaseItem->id)->get();


            // Update referenced order-items.
            $savedPurchaseItem->updateStatusOfRelatedOrderItems();



            // BMD-DELETE
            $updatedOrderItems = OrderItem::where('purchase_item_id', $savedPurchaseItem->id)->get();
            $oldIi = InventoryItem::where('size_availability_id', $savedPurchaseItem->size_availability_id)->get()[0];


            // Update relevant inventory-items.
            $updatedIi = $savedPurchaseItem->updateStatsOfRelatedInventoryItem();


            // Commit db-transaction.
            DB::commit();
        }



        return [
            'isResultOk' => $isResultOk,
            'resultCode' => $resultCode,
            'objs' => [
                'savedPurchaseItem' => new PurchaseItemResource($savedPurchaseItem),
                // BMD-DELETE
                'oldOrderItems' => $oldOrderItems,
                'updatedOrderItems' => $updatedOrderItems,
                'oldIi' => $oldIi,
                'updatedIi' => $updatedIi
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
            'quantity' => 'required|integer|min:1',
            'projectedPrice' => 'required|numeric',
            'actualPrice' => 'nullable|numeric',
            'statusCode' => 'required|exists:purchase_item_status,code'
        ]);
    }
}
