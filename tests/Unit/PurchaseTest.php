<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\OrderStatus;
use Database\Seeders\OrderStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;



    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrderStatusSeeder::class);
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
}
