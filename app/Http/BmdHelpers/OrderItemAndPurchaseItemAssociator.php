<?php

namespace App\Http\BmdHelpers;

use App\Models\Seller;
use App\Models\Purchase;
use App\Models\OrderStatus;
use App\Models\PurchaseItem;
use App\Models\InventoryItem;
use App\Models\ProductSeller;
use App\Models\OrderItemStatus;
use App\Models\PurchaseItemStatus;
use Illuminate\Support\Facades\DB;
use App\Bmd\Generals\GeneralHelper;
use App\Http\BmdHttpResponseCodes\GeneralHttpResponseCodes;
use App\Http\BmdHttpResponseCodes\OrderItemHttpResponseCodes;

class OrderItemAndPurchaseItemAssociator
{
    public static function associate($order)
    {
        if (!self::isOrderWithStatusAllowedForPurchaseAssociations($order)) {
            return OrderItemHttpResponseCodes::NOT_ALLOWED_FOR_PURCHASE_ASSOCIATIONS;
        }


        foreach ($order->orderItems as $oi) {

            if (!self::isOrderItemWithStatusAllowedForPurchaseItemAssociation($oi)) {
                continue;
            }

            DB::beginTransaction();

            $entireProcessData = [
                'orderItem' => $oi,
                'sellerProduct' => ProductSeller::find($oi->product_seller_id),
                'purchase' => null, // to-be-set
                'purchaseItem' => null // to-be-set
            ];

            self::setReferencedPurchase($entireProcessData);
            self::setReferencedPurchaseItem($entireProcessData);
            self::setSpecificInventoryItemStat($entireProcessData);
            self::setOrderItem($entireProcessData);

            DB::commit();
        }


        return GeneralHttpResponseCodes::OK;
    }



    public static function setOrderItem(&$entireProcessData)
    {
        $d = $entireProcessData;

        // Update OrderItem.
        $d['orderItem']->purchase_item_id = $d['purchaseItem']->id;
        $d['orderItem']->status_code = OrderItemStatus::where('name', OrderItemStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0]->code;
        $d['orderItem']->save();
    }



    public static function setSpecificInventoryItemStat(&$entireProcessData)
    {
        $d = $entireProcessData;

        // Update the specific InventoryItem's stats.
        $ii = InventoryItem::where('seller_product_id', $d['sellerProduct']->id)
            ->where('size_availability_id', $d['orderItem']->size_availability_id)
            ->get()[0] ?? null;


        if (!$ii) {
            $seller = Seller::find($d['sellerProduct']->seller_id);

            $ii = new InventoryItem();
            $ii->product_id = $d['orderItem']->product_id;
            $ii->seller_id = $seller->id;
            $ii->seller_product_id = $d['sellerProduct']->id;
            $ii->size_availability_id = $d['orderItem']->size_availability_id;
        }

        $ii->to_be_purchased_quantity += $d['orderItem']->quantity;
        $ii->save();
    }



    public static function setReferencedPurchaseItem(&$entireProcessData)
    {
        $d = $entireProcessData;

        // Reference the purchase-item.
        $pi = PurchaseItem::where('purchase_id', $d['purchase']->id)
            ->where('seller_product_id', $d['sellerProduct']->id)
            ->where('size_availability_id', $d['orderItem']->size_availability_id)
            ->get()[0] ?? null;

        if (!$pi) {
            $pi = new PurchaseItem();
            $pi->purchase_id = $d['purchase']->id;
            $pi->seller_product_id = $d['sellerProduct']->id;
            $pi->size_availability_id = $d['orderItem']->size_availability_id;
            $pi->projected_price = $d['orderItem']->price;
            $pi->status_code = PurchaseItemStatus::where('name', PurchaseItemStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0]->code;
        }

        // Update the purchase-item's quantity.
        $pi->quantity += $d['orderItem']->quantity;
        $pi->save();


        $entireProcessData['purchaseItem'] = $pi;
    }



    /**
     * Set the referenced purchase obj for the entire process.
     */
    public static function setReferencedPurchase(&$entireProcessData)
    {
        $d = $entireProcessData;

        // Reference the purchase. Either create or update the purchase.
        $purchaseDateStr = GeneralHelper::getDateInStrWithUnixTimestamp(getdate()[0]); // Date today.

        if (BmdOperationsTimeManager::isNowPassedTodaysPurchaseBuyingTime()) {
            $purchaseDateStr = GeneralHelper::getDateInStrWithUnixTimestamp(getdate()[0], 1); // Date tomorrow.
        }


        $seller = Seller::find($d['sellerProduct']->seller_id);

        if (Purchase::isTherePurchaseRecordWithSellerForDate($seller, $purchaseDateStr)) {

            $entireProcessData['purchase'] = Purchase::getPurchaseWithSellerIdForDate($seller->id, $purchaseDateStr);
        } else {

            $purchaseDatetimeStr = $purchaseDateStr . ' 01:00:00';

            $entireProcessData['purchase'] = Purchase::createWithData([
                'seller_id' => $seller->id,
                'created_at' => $purchaseDatetimeStr
            ]);
        }
    }



    public static function isOrderWithStatusAllowedForPurchaseAssociations($order)
    {

        $statusCodeForOrderSummaryEmailSentToCustomer = OrderStatus::getCodeByName('ORDER_DETAILS_EMAILED_TO_USER');
        $statusCodeForOrderBeingEvaluatedForPurchase = OrderStatus::getCodeByName('BEING_EVALUATED_FOR_PURCHASE');


        if (
            $order->status_code == $statusCodeForOrderSummaryEmailSentToCustomer
            || $order->status_code == $statusCodeForOrderBeingEvaluatedForPurchase
        ) {
            return true;
        }

        return false;
    }



    public static function isOrderItemWithStatusAllowedForPurchaseItemAssociation($orderItem)
    {
        $defaultStatus = OrderItemStatus::where('name', OrderItemStatus::NAME_FOR_STATUS_DEFAULT)->get()[0];
        $toBePurchasedStatus = OrderItemStatus::where('name', OrderItemStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0];

        if (
            $orderItem->status_code == $defaultStatus->code
            || $orderItem->status_code == $toBePurchasedStatus->code
            || !isset($orderItem->purchase_item_id)
        ) {
            return true;
        }

        return false;
    }
}
