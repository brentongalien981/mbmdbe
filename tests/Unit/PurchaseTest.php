<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\OrderItemStatus;
use Database\Seeders\SellerSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\OrderStatusSeeder;
use Database\Seeders\ProductSellerSeeder;
use Database\Seeders\PurchaseStatusSeeder;
use Database\Seeders\OrderItemStatusSeeder;
use Database\Seeders\SizeAvailabilitySeeder;
use Database\Seeders\PurchaseItemStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
            PurchaseItemStatusSeeder::class
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
            'created_at' => $ordersStartDateInStr . '09:00:00'
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
            'created_at' => $ordersStartDateInStr . '09:00:00'
        ]);

        $ordersAllowedForPurchase2 = Order::factory()->count(5)->create([
            'created_at' => $ordersStartDateInStr . '09:00:00',
            'status_code' => $statusCodeForOrderDetailsEmailedToUser
        ]);

        $ordersNotAllowedForPurchase = Order::factory()->count(5)->create([
            'created_at' => $ordersStartDateInStr . '10:00:00',
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
        $this->seed([
            ProductSeeder::class,
            SellerSeeder::class,
            ProductSellerSeeder::class,
            SizeAvailabilitySeeder::class
        ]);        

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
                'created_at' => $datesInStrForOrderYesterday['ordersStartDateInStr'] . '09:00:00'
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
            if ($oi->id == $notAllowedOrderItem->id) { continue; }
            $this->assertTrue($oi->status_code == OrderItemStatus::getCodeByName('TO_BE_PURCHASED'));
        }

        $this->assertEquals(OrderItemStatus::getCodeByName('TOO_LATE_TO_DELIVER'), $notAllowedOrderItem->status_code);
        $this->assertEquals(6, $updatedOrder->orderItems()->count());
        $this->assertCount(1, Order::all());
    }



    /** @test */
    public function it_only_creates_purchase_record_from_a_specific_seller_once_each_day()
    {
        // BMD-TODO:
        $this->assertTrue(false);
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
