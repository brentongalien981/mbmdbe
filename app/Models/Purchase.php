<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{
    use HasFactory;


    public static function getTodaysPurchases()
    {
        $dateObjToday = getdate();

        $startDateTodayInStr = $dateObjToday['year'] . '-' . $dateObjToday['mon'] . '-' . $dateObjToday['mday'];
        $endDateTodayInStr = $startDateTodayInStr . ' 23:59:59';


        $purchasesToday = self::where('created_at', '>=', $startDateTodayInStr)
            ->where('created_at', '<=', $endDateTodayInStr)
            ->get();

        return $purchasesToday;
    }



    public static function updateTodaysPurchasesStatus() {
        $todaysPurchases = self::getTodaysPurchases();

        foreach ($todaysPurchases as $p) {
            $p->updatePurchaseStatus();
        }
    }



    public function updatePurchaseStatus() {

        $purchaseItems = $this->purchaseItems;
        $numOfPurchaseItems = count($purchaseItems);
        $numOfPurchaseItemsWithToBePurchasedStatus = 0;


        foreach ($purchaseItems as $pi) {
            $toBePurchasedStatus = PurchaseItemStatus::where('name', PurchaseItemStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0];
            if ($pi->status_code == $toBePurchasedStatus->code) {
                ++$numOfPurchaseItemsWithToBePurchasedStatus;
            }
        }


        if ($numOfPurchaseItemsWithToBePurchasedStatus == $numOfPurchaseItems) {
            $this->status_code = PurchaseStatus::where('name', PurchaseStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0]->code;
        } else if ($numOfPurchaseItemsWithToBePurchasedStatus == 0) {
            $this->status_code = PurchaseStatus::where('name', PurchaseStatus::NAME_FOR_STATUS_DEFAULT)->get()[0]->code;
        } else {
            $this->status_code = PurchaseStatus::where('name', PurchaseStatus::NAME_FOR_STATUS_EVALUATED_INCOMPLETELY_FOR_PURCHASE)->get()[0]->code;
        }

        $this->save();
    }



    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }



    public static function prepareBmdPurchases($ordersStartDateInStr, $ordersEndDateInStr)
    {

        $ordersEndDateInStr = $ordersEndDateInStr . ' 23:59:59';

        $orders = Order::where('created_at', '>=', $ordersStartDateInStr)
            ->where('created_at', '<=', $ordersEndDateInStr)
            ->get();


        foreach ($orders as $o) {


            $statusCodeForOrderSummaryEmailSentToCustomer = OrderStatus::getCodeByName('ORDER_DETAILS_EMAILED_TO_USER');
            $statusCodeForOrderBeingEvaluatedForPurchase = OrderStatus::getCodeByName('BEING_EVALUATED_FOR_PURCHASE');

            // Skip orders that were paid, but not been emailed to customer with its order-summary.
            if ($o->status_code != $statusCodeForOrderSummaryEmailSentToCustomer
                || $o->status_code != $statusCodeForOrderBeingEvaluatedForPurchase) {
                continue;
            }

            $o->status_code = $statusCodeForOrderBeingEvaluatedForPurchase;


            foreach ($o->orderItems as $oi) {

                $oiDefaultStatus = OrderItemStatus::where('name', OrderItemStatus::NAME_FOR_STATUS_DEFAULT)->get()[0];
                if ($oi->status_code != $oiDefaultStatus->code) {
                    continue;
                }

                $sellerProduct = ProductSeller::find($oi->product_seller_id);
                $seller = Seller::find($sellerProduct->seller_id);

                DB::beginTransaction();

                if (self::doTodaysPurchasesAlreadyIncludeFromSeller($seller)) {

                    // Update purchase-item's qty.
                    $p = self::getPurchaseWithSellerId($seller->id);
                    $pi = PurchaseItem::where('purchase_id', $p->id)
                        ->where('seller_product_id', $sellerProduct->id)
                        ->where('size_availability_id', $oi->size_availability_id)
                        ->get();

                    if (isset($pi) && isset($pi[0])) {
                        $pi = $pi[0];
                    } else {
                        // Create PurchaseItem.
                        $pi = new PurchaseItem();
                        $pi->purchase_id = $p->id;
                        $pi->seller_product_id = $sellerProduct->id;
                        $pi->size_availability_id = $oi->size_availability_id;
                        $pi->projected_price = $oi->price;
                        $pi->status_code = PurchaseItemStatus::where('name', PurchaseItemStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0]->code;
                    }

                    $pi->quantity += $oi->quantity;
                    $pi->save();
                } else {

                    // Create Purchase.
                    $p = new Purchase();
                    $p->seller_id = $seller->id;
                    $p->status_code = PurchaseStatus::where('name', PurchaseStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0]->code;
                    $p->save();


                    // Create PurchaseItem.
                    $pi = new PurchaseItem();
                    $pi->purchase_id = $p->id;
                    $pi->seller_product_id = $sellerProduct->id;
                    $pi->size_availability_id = $oi->size_availability_id;
                    $pi->quantity = $oi->quantity;
                    $pi->projected_price = $oi->price;
                    $pi->status_code = PurchaseItemStatus::where('name', PurchaseItemStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0]->code;
                    $pi->save();
                }


                // Update the InventoryItem's stats.
                $ii = InventoryItem::where('seller_product_id', $sellerProduct->id)
                    ->where('size_availability_id', $oi->size_availability_id)
                    ->get();

                if (isset($ii) && isset($ii[0])) {
                    $ii = $ii[0];
                } else {
                    $ii = new InventoryItem();
                    $ii->product_id = $oi->product_id;
                    $ii->seller_id = $seller->id;
                    $ii->seller_product_id = $sellerProduct->id;
                    $ii->size_availability_id = $oi->size_availability_id;
                }

                $ii->to_be_purchased_quantity += $oi->quantity;
                $ii->save();



                // Update OrderItem's status.
                $oi->status_code = OrderItemStatus::where('name', OrderItemStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0]->code;
                $oi->save();


                DB::commit();
            }


            $o->status_code = OrderStatus::getCodeByName('TO_BE_PURCHASED');
        }
    }



    public static function doTodaysPurchasesAlreadyIncludeFromSeller($seller)
    {
        $todaysPurchases = self::getTodaysPurchases();

        foreach ($todaysPurchases as $p) {
            if ($p->seller_id == $seller->id) {
                return true;
            }
        }

        return false;
    }



    public static function getPurchaseWithSellerId($sellerId)
    {
        $todaysPurchases = self::getTodaysPurchases();

        foreach ($todaysPurchases as $p) {
            if ($p->seller_id == $sellerId) {
                return $p;
            }
        }

        return null;
    }
}
