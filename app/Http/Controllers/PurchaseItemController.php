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
use App\Http\Resources\PurchaseItemResource;
use App\Rules\UniqueSizeAvailabilityForPurchase;
use App\Rules\SizeAvailabilityBelongsToSellerProduct;
use App\Rules\PurchaseItemSellerIdEqualsPurchaseSellerId;
use App\Http\BmdHttpResponseCodes\PurchaseHttpResponseCodes;
use App\Http\BmdHttpResponseCodes\PurchaseItemHttpResponseCodes;

class PurchaseItemController extends Controller
{
    public function store(Request $r) { return $this->save($r, 'store'); }
    public function update(Request $r) { return $this->save($r, 'update'); }



    public function save(Request $r, $crudAction)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Purchase::class);

        $v = $this->validateRequestData($r, $crudAction);

        $isResultOk = true;
        $resultCode = null;

        $extraValidationData = [
            'purchaseId' => $v['purchaseId'],
            'sellerProductId' => $v['sellerProductId'],
            'sizeAvailabilityId' => $v['sizeAvailabilityId'],
            'purchaseItemId' => $v['id'] ?? null
        ];


        try {
            if (!PurchaseItemSellerIdEqualsPurchaseSellerId::bmdValidate($extraValidationData)) {
                $resultCode = PurchaseItemHttpResponseCodes::PURCHASE_ITEM_SELLER_SHOULD_EQUAL_PURCHASE_SELLER;
                throw new Exception();
            }

            if (!UniqueSizeAvailabilityForPurchase::bmdValidate($extraValidationData)) {
                $resultCode = PurchaseHttpResponseCodes::PURCHASE_SHOULD_HAVE_UNIQUE_SIZE_AVAILABILITIES;
                throw new Exception();
            }

            if (!SizeAvailabilityBelongsToSellerProduct::bmdValidate($extraValidationData)) {
                $resultCode = PurchaseItemHttpResponseCodes::SIZE_AVAILABILITY_DOES_NOT_BELONG_TO_SELLER_PRODUCT;
                throw new Exception();
            }
        } catch (Exception $e) {
            $isResultOk = false;
        }



        $savedPurchaseItem = null;        
        $oldPurchaseItemStatusCode = null;

        if ($crudAction == 'update') {
            $oldPurchaseItemStatusCode = PurchaseItem::find($v['id'])->status_code;
        }



        if ($isResultOk) {
            
            DB::beginTransaction();

            $savedPurchaseItem = PurchaseItem::saveWithData($v, $crudAction);

            $savedPurchaseItem->updateStatusOfRelatedOrderItems();

            $savedPurchaseItem->updateStatsOfRelatedInventoryItem($crudAction, $oldPurchaseItemStatusCode);

            DB::commit();

            $savedPurchaseItem = $savedPurchaseItem ? new PurchaseItemResource($savedPurchaseItem) : null;
        }        


        return [
            'isResultOk' => $isResultOk,
            'resultCode' => $resultCode,
            'objs' => [
                'savedPurchaseItem' => $savedPurchaseItem
            ]
        ];
    }



    private function validateRequestData(Request $r, $crudAction = 'store')
    {
        $idValidationRule = ($crudAction === 'store' ? 'nullable' : 'required|integer');

        return $r->validate([
            'id' => $idValidationRule,
            'purchaseId' => 'required|exists:purchases,id',
            'sellerProductId' => 'required|exists:product_seller,id',
            'sizeAvailabilityId' => 'required|exists:size_availabilities,id',
            'quantity' => 'required|integer|min:1',
            'projectedPrice' => 'required|numeric',
            'actualPrice' => 'nullable|numeric',
            'statusCode' => 'required|exists:purchase_item_status,code'
        ]);
    }
}
