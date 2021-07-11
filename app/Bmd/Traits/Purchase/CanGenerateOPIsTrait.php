<?php

namespace App\Bmd\Traits\Purchase;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Bmd\Generals\GeneralHelper;

trait CanGenerateOPIsTrait
{


    public static function generateBmdPurchases($ordersStartDateInStr, $ordersEndDateInStr)
    {

        $numOfOrderDays = GeneralHelper::getNumDaysBetweenDates($ordersStartDateInStr, $ordersEndDateInStr);

        for ($i = 0; $i < $numOfOrderDays; $i++) {

            $currentIthDate = GeneralHelper::getDateInStrWithData($ordersStartDateInStr, $i);
            $nextIthDate = GeneralHelper::getDateInStrWithData($ordersStartDateInStr, $i + 1);
            $nextIthDate .= ' 23:59:59';


            $orders = Order::where('created_at', '>=', $currentIthDate)
                ->where('created_at', '<=', $nextIthDate)
                ->get();


            foreach ($orders as $o) {

                $statusCodeForOrderBeingEvaluatedForPurchase = OrderStatus::getCodeByName('BEING_EVALUATED_FOR_PURCHASE');


                if (
                    $o->status_code == $statusCodeForOrderSummaryEmailSentToCustomer
                    || $o->status_code == $statusCodeForOrderBeingEvaluatedForPurchase
                ) {

                    $o->status_code = $statusCodeForOrderBeingEvaluatedForPurchase;
                    $o->save();


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



                        // Update OrderItem.
                        $oi->purchase_item_id = $pi->id;
                        $oi->status_code = OrderItemStatus::where('name', OrderItemStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0]->code;
                        $oi->save();


                        DB::commit();
                    }
                }
            }
        }
    }
}
