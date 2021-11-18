<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;

use App\Bmd\Generals\GeneralHelper2;
use Tests\TestCase;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\OrderItemStatus;
use App\Models\PurchaseItem;
use Database\Seeders\SellerSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\OrderStatusSeeder;
use Database\Seeders\ProductSellerSeeder;
use Database\Seeders\PurchaseStatusSeeder;
use Database\Seeders\OrderItemStatusSeeder;
use Database\Seeders\SizeAvailabilitySeeder;
use Database\Seeders\PurchaseItemStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function PHPUnit\Framework\assertTrue;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;



    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            OrderStatusSeeder::class,
            OrderItemStatusSeeder::class,
            PurchaseStatusSeeder::class,
            PurchaseItemStatusSeeder::class,

            ProductSeeder::class,
            SellerSeeder::class,
            ProductSellerSeeder::class,
            SizeAvailabilitySeeder::class
        ]);
    }



    /** @test */
    public function it_only_prepares_bmd_purchases_for_orders_yesterday()
    {
        $this->withoutExceptionHandling();

        $numOfSecInDay = 86400;
        $dateObjToday = getdate();
        $dateObjYesterday = getdate($dateObjToday[0] - $numOfSecInDay);

        $startDateObj = getdate($dateObjYesterday[0]);
        $endDataObj = getdate($dateObjYesterday[0]);

        $ordersStartDateInStr = $startDateObj['year'] . '-' . $startDateObj['mon'] . '-' . $startDateObj['mday'];
        $ordersEndDateInStr = $endDataObj['year'] . '-' . $endDataObj['mon'] . '-' . $endDataObj['mday'];


        $ordersMultipleDaysAgo = Order::factory()->count(5)->create([
            'created_at' => '2021-11-09 11:00:00'
        ]);

        $ordersYesterday = Order::factory()->count(5)->create([
            'created_at' => $ordersStartDateInStr . ' 09:00:00'
        ]);



        Purchase::prepareBmdPurchases($ordersStartDateInStr, $ordersEndDateInStr);
        Purchase::prepareBmdPurchases($ordersStartDateInStr, $ordersEndDateInStr);


        $statusCodeForBeingEvaluatedForPurchase = OrderStatus::getCodeByName('BEING_EVALUATED_FOR_PURCHASE');
        $statusCodeForOrderToBePurchased = OrderStatus::getCodeByName('TO_BE_PURCHASED');

        $ordersWithStatusBeingEvaluatedForPurchase = Order::where('status_code', $statusCodeForBeingEvaluatedForPurchase)->get();
        $ordersWithStatusToBePurchased = Order::where('status_code', $statusCodeForOrderToBePurchased)->get();

        $this->assertEquals(5, $ordersWithStatusBeingEvaluatedForPurchase->count());
        $this->assertEquals(5, $ordersWithStatusToBePurchased->count());
        $this->assertEquals(10, Order::all()->count());
    }



    /** @test */
    public function it_only_prepares_bmd_purchases_for_orders_with_certain_statuses()
    {

        $numOfSecInDay = 86400;
        $dateObjToday = getdate();
        $dateObjYesterday = getdate($dateObjToday[0] - $numOfSecInDay);

        $startDateObj = getdate($dateObjYesterday[0]);
        $endDataObj = getdate($dateObjYesterday[0]);

        $ordersStartDateInStr = $startDateObj['year'] . '-' . $startDateObj['mon'] . '-' . $startDateObj['mday'];
        $ordersEndDateInStr = $endDataObj['year'] . '-' . $endDataObj['mon'] . '-' . $endDataObj['mday'];


        $statusCodeForOrderDetailsEmailedToUser = OrderStatus::getCodeByName('ORDER_DETAILS_EMAILED_TO_USER');
        $statusCodeForOrderToBePurchased = OrderStatus::getCodeByName('TO_BE_PURCHASED');
        $statusCodeForOrderDelivered = OrderStatus::getCodeByName('DELIVERED');


        $ordersAllowedForPurchase1 = Order::factory()->count(5)->create([
            'created_at' => $ordersStartDateInStr . ' 09:00:00'
        ]);

        $ordersAllowedForPurchase2 = Order::factory()->count(5)->create([
            'created_at' => $ordersStartDateInStr . ' 09:00:00',
            'status_code' => $statusCodeForOrderDetailsEmailedToUser
        ]);

        $ordersNotAllowedForPurchase = Order::factory()->count(5)->create([
            'created_at' => $ordersStartDateInStr . ' 10:00:00',
            'status_code' => $statusCodeForOrderDelivered
        ]);


        Purchase::prepareBmdPurchases($ordersStartDateInStr, $ordersEndDateInStr);

        $ordersWithStatusToBePurchased = Order::where('status_code', $statusCodeForOrderToBePurchased)->get();
        $ordersWithStatusDelivered = Order::where('status_code', $statusCodeForOrderDelivered)->get();

        $this->assertEquals(10, $ordersWithStatusToBePurchased->count());
        $this->assertEquals(5, $ordersWithStatusDelivered->count());
        $this->assertEquals(15, Order::all()->count());
    }



    /** @test */
    public function it_only_processes_order_items_with_certain_statuses_when_preparing_bmd_purchases()
    {

        $datesInStrForOrderYesterday = $this->getOrderStartAndEndDateForYesterday();


        // Given you have orders with order-items that have statuses allowed for preparing bmd-purchases.
        $order = Order::factory()
            ->has(
                OrderItem::factory()
                    ->count(5)
                    ->state(function (array $attributes, Order $order) {
                        return ['order_id' => $order->id];
                    })
            )
            ->create([
                'created_at' => $datesInStrForOrderYesterday['ordersStartDateInStr'] . ' 09:00:00'
            ]);

        $notAllowedOrderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'status_code' => OrderItemStatus::getCodeByName('TOO_LATE_TO_DELIVER')
        ]);


        // When you prepare-bmd-purchases for today...        
        Purchase::prepareBmdPurchases($datesInStrForOrderYesterday['ordersStartDateInStr'], $datesInStrForOrderYesterday['ordersEndDateInStr']);


        // Then those order-items should have an updated status of TO_BE_PURCHASED.
        $updatedOrder = Order::find($order->id);

        foreach ($updatedOrder->orderItems as $oi) {
            // dd($oi->status_code);
            if ($oi->id == $notAllowedOrderItem->id) {
                continue;
            }
            $this->assertTrue($oi->status_code == OrderItemStatus::getCodeByName('TO_BE_PURCHASED'));
        }

        $this->assertEquals(OrderItemStatus::getCodeByName('TOO_LATE_TO_DELIVER'), $notAllowedOrderItem->status_code);
        $this->assertEquals(6, $updatedOrder->orderItems()->count());
        $this->assertCount(1, Order::all());
    }



    /** @test */
    public function it_only_creates_purchase_record_from_a_specific_seller_at_max_once_each_day()
    {
        // Given we have orders with multiple order-items...
        $datesInStrForOrderYesterday = $this->getOrderStartAndEndDateForYesterday();


        // Given you have orders with order-items that have statuses allowed for preparing bmd-purchases.
        $orders = Order::factory()
            ->count(5)
            ->has(
                OrderItem::factory()
                    ->count(3)
                    ->state(function (array $attributes, Order $order) {
                        return ['order_id' => $order->id];
                    })
            )
            ->create([
                'created_at' => $datesInStrForOrderYesterday['ordersStartDateInStr'] . ' 09:00:00'
            ]);



        // If system does prepare-bmd-purchases,
        Purchase::prepareBmdPurchases($datesInStrForOrderYesterday['ordersStartDateInStr'], $datesInStrForOrderYesterday['ordersEndDateInStr']);



        // Then there should only be one purchase-record for each seller on that day.
        $purchasesToday = Purchase::getTodaysPurchases();
        $sellerIdsForPurchasesToday = [];

        foreach ($purchasesToday as $p) {
            $this->assertFalse(in_array($p->seller_id, $sellerIdsForPurchasesToday));
            $sellerIdsForPurchasesToday[] = $p->seller_id;
        }

        $this->assertEquals($purchasesToday->count(), count($sellerIdsForPurchasesToday));
        $this->assertEquals(5, $orders->count());



        // Then the order-items' signature foreign-key ids should be the same as the purchase-items' foreign-key ids.
        $quantityOfAllOrderItemsBySizeAvailability = [];

        foreach ($orders as $o) {
            foreach ($o->orderItems as $oi) {
                $pi = PurchaseItem::where('id', $oi->purchase_item_id)->where('seller_product_id', $oi->product_seller_id)->where('size_availability_id', $oi->size_availability_id)->get();
                $this->assertEquals(1, $pi->count());

                $sizeAvailIdInStr = strval($oi->size_availability_id);

                if (isset($quantityOfAllOrderItemsBySizeAvailability[$sizeAvailIdInStr])) {
                    $quantityOfAllOrderItemsBySizeAvailability[$sizeAvailIdInStr] += $oi->quantity;
                } else {
                    $quantityOfAllOrderItemsBySizeAvailability[$sizeAvailIdInStr] = $oi->quantity;
                }
            }
        }


        // Then the purchase-items-total quantity should appropriately equal to the order-items total quantity.
        foreach ($purchasesToday as $p) {
            foreach ($p->purchaseItems as $pi) {
                $sizeAvailIdInStr = strval($pi->size_availability_id);
                $this->assertEquals($pi->quantity, $quantityOfAllOrderItemsBySizeAvailability[$sizeAvailIdInStr]);
            }
        }

        // dd($quantityOfAllOrderItemsBySizeAvailability);

    }



    /** @test */
    public function it_still_only_creates_purchase_record_from_a_specific_seller_at_max_once_each_day_even_with_similar_orders()
    {
        $datesInStrForOrderYesterday = $this->getOrderStartAndEndDateForYesterday();

        // Given there are multiple orders but all similar order-items' signatures (the same foreign-key ids)        
        $orders = Order::factory()->count(10)->create(['created_at' => $datesInStrForOrderYesterday['ordersStartDateInStr'] . ' 09:00:00']);
        $orderItemTemplate = OrderItem::factory()->make();

        foreach ($orders as $o) {
            $oi = OrderItem::create([
                'order_id' => $o->id,
                'product_id' => $orderItemTemplate->product_id,
                'product_seller_id' => $orderItemTemplate->product_seller_id,
                'size_availability_id' => $orderItemTemplate->size_availability_id,
                'purchase_item_id' => $orderItemTemplate->purchase_item_id,
                'price' => $orderItemTemplate->price,
                'quantity' => $orderItemTemplate->quantity,
                'status_code' => $orderItemTemplate->status_code,
            ]);
        }



        // If system does prepare-bmd-purchases,        
        Purchase::prepareBmdPurchases($datesInStrForOrderYesterday['ordersStartDateInStr'], $datesInStrForOrderYesterday['ordersEndDateInStr']);



        // Then there should only be one purchase-record for that day.
        $purchasesToday = Purchase::getTodaysPurchases();

        $this->assertEquals(10, Order::getYesterdaysOrders()->count());
        $this->assertEquals(1, $purchasesToday->count());


        // Then the order-items' signature foreign-key ids should be the same as the purchase-items' foreign-key ids.
        $quantityOfAllOrderItemsBySizeAvailability = [];

        foreach ($orders as $o) {
            foreach ($o->orderItems as $oi) {
                $pi = PurchaseItem::where('id', $oi->purchase_item_id)->where('seller_product_id', $oi->product_seller_id)->where('size_availability_id', $oi->size_availability_id)->get();
                $this->assertEquals(1, $pi->count());

                $sizeAvailIdInStr = strval($oi->size_availability_id);

                if (isset($quantityOfAllOrderItemsBySizeAvailability[$sizeAvailIdInStr])) {
                    $quantityOfAllOrderItemsBySizeAvailability[$sizeAvailIdInStr] += $oi->quantity;
                } else {
                    $quantityOfAllOrderItemsBySizeAvailability[$sizeAvailIdInStr] = $oi->quantity;
                }
            }
        }


        // Then the purchase-items-total quantity should appropriately equal to the order-items total quantity.
        foreach ($purchasesToday as $p) {
            foreach ($p->purchaseItems as $pi) {
                $sizeAvailIdInStr = strval($pi->size_availability_id);
                $this->assertEquals($pi->quantity, $quantityOfAllOrderItemsBySizeAvailability[$sizeAvailIdInStr]);
            }
        }
    }



    /** Helper Funcs */
    private function getOrderStartAndEndDateForYesterday()
    {
        $numOfSecInDay = 86400;
        $dateObjToday = getdate();
        $dateObjYesterday = getdate($dateObjToday[0] - $numOfSecInDay);

        $startDateObj = getdate($dateObjYesterday[0]);
        $endDataObj = getdate($dateObjYesterday[0]);

        return [
            'ordersStartDateInStr' => $startDateObj['year'] . '-' . $startDateObj['mon'] . '-' . $startDateObj['mday'],
            'ordersEndDateInStr' => $endDataObj['year'] . '-' . $endDataObj['mon'] . '-' . $endDataObj['mday']
        ];
    }
}
