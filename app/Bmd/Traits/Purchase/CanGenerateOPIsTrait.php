<?php

namespace App\Bmd\Traits\Purchase;

use App\Models\Order;
use App\Models\Seller;
use App\Models\Purchase;
use App\Models\OrderStatus;
use App\Models\PurchaseItem;
use App\Models\InventoryItem;
use App\Models\ProductSeller;
use App\Models\PurchaseStatus;
use App\Models\OrderItemStatus;
use App\Models\PurchaseItemStatus;
use Illuminate\Support\Facades\DB;
use App\Bmd\Generals\GeneralHelper;
use App\Listeners\HandleGenerateOPIsEvent;

trait CanGenerateOPIsTrait
{


    public static function generateBmdPurchases($ordersStartDateInStr, $ordersEndDateInStr, HandleGenerateOPIsEvent $eHandler)
    {

        $numOfOrderDays = GeneralHelper::getNumDaysBetweenDates($ordersStartDateInStr, $ordersEndDateInStr) + 1;

        for ($i = 0; $i < $numOfOrderDays; $i++) {

            $ithStartDate = GeneralHelper::getDateInStrWithData($ordersStartDateInStr, $i);
            $ithEndDate = $ithStartDate . ' 23:59:59';


            $orders = Order::where('created_at', '>=', $ithStartDate)
                ->where('created_at', '<=', $ithEndDate)
                ->get();


            foreach ($orders as $o) {

                $statusCodeForOrderBeingEvaluatedForPurchase = OrderStatus::getCodeByName('BEING_EVALUATED_FOR_PURCHASE');

                if ($o->status_code == $statusCodeForOrderBeingEvaluatedForPurchase) {

                    foreach ($o->orderItems as $oi) {

                        $oiDefaultStatus = OrderItemStatus::where('name', OrderItemStatus::NAME_FOR_STATUS_DEFAULT)->get()[0];
                        if (
                            $oi->status_code != $oiDefaultStatus->code
                            || isset($oi->purchase_item_id)
                        ) {
                            continue;
                        }

                        $sellerProduct = ProductSeller::find($oi->product_seller_id);
                        $seller = Seller::find($sellerProduct->seller_id);
                        $p = null;
                        $pi = null;

                        DB::beginTransaction();

                        $nextDate = GeneralHelper::getDateInStrWithData($ithStartDate, 1);

                        if (self::arePurchasesAlreadyIncludedFromSellerForDate($seller, $nextDate)) {

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
                                $pi->status_code = PurchaseItemStatus::where('name', PurchaseItemStatus::NAME_FOR_STATUS_DISPATCHED)->get()[0]->code;
                            }

                            $pi->quantity += $oi->quantity;
                            
                        } else {

                            // Create Purchase.
                            $p = new Purchase();
                            $p->seller_id = $seller->id;
                            $p->status_code = PurchaseStatus::where('name', PurchaseStatus::NAME_FOR_STATUS_DISPATCHED)->get()[0]->code;
                            $p->created_at = $nextDate;
                            $p->save();


                            // Create PurchaseItem.
                            $pi = new PurchaseItem();
                            $pi->purchase_id = $p->id;
                            $pi->seller_product_id = $sellerProduct->id;
                            $pi->size_availability_id = $oi->size_availability_id;
                            $pi->quantity = $oi->quantity;
                            $pi->projected_price = $oi->price;
                            $pi->status_code = PurchaseItemStatus::where('name', PurchaseItemStatus::NAME_FOR_STATUS_DISPATCHED)->get()[0]->code;
                        }


                        $pi->created_at = $nextDate;
                        $pi->save();
                        


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

                        $ii->dispatched_quantity += $oi->quantity;
                        $ii->save();



                        // Update OrderItem.
                        $oi->purchase_item_id = $pi->id;
                        $oi->status_code = OrderItemStatus::where('name', OrderItemStatus::NAME_FOR_STATUS_DISPATCHED)->get()[0]->code;
                        $oi->save();


                        DB::commit();
                    }


                    $statusCodeForDelivered = OrderStatus::getCodeByName('DELIVERED');
                    $o->status_code = $statusCodeForDelivered;
                    $o->save();
                }
            }


            $eHandler->updateLogs([
                'ithDayOfPurchaseCreation' => $i+1,
                'isForCheckpointUpdate' => true
            ]);
        }

    }



    /**
     * NOTE: That purchase dates are generally always one day ahead of orders' dates.
     *
     * @param Seller $seller
     * @param string $date
     * @return bool
     */
    public static function arePurchasesAlreadyIncludedFromSellerForDate($seller, $date)
    {
        $purchases = self::getPurchasesForDate($date);

        foreach ($purchases as $p) {
            if ($p->seller_id == $seller->id) {
                return true;
            }
        }

        return false;
    }



    public static function getPurchasesForDate($date)
    {
        $startDate = $date;
        $endDate = $date. ' 23:59:59';


        $purchases = self::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get();

        return $purchases;
    }



    public static function getTodaysPurchases()
    {
        $startDateTodayInStr = GeneralHelper::getTodaysDateInStr();
        $endDateTodayInStr = $startDateTodayInStr . ' 23:59:59';

        $purchases = self::where('created_at', '>=', $startDateTodayInStr)
            ->where('created_at', '<=', $endDateTodayInStr)
            ->get();

        return $purchases;
    }



    public static function updateTodaysPurchasesStatus()
    {
        $todaysPurchases = self::getTodaysPurchases();

        foreach ($todaysPurchases as $p) {
            $p->updatePurchaseStatusBasedOnPurchaseItemsStatuses();
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



}
