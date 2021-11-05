<?php

namespace Tests\Unit\BmdHelpers;

use App\Bmd\Helpers\OrderItemAndPurchaseItemAssociator;
use App\Models\Order;
use App\Models\OrderStatus;
use Database\Seeders\OrderStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderItemAndPurchaseItemAssociatorTest extends TestCase
{
    use RefreshDatabase;



    /** @test */
    public function it_guards_associations_depending_on_order_status()
    {
        $this->seed(OrderStatusSeeder::class);

        $notAllowedOrder = Order::factory()->create([
            'status_code' => OrderStatus::getCodeByName('START_OF_FINALIZING_ORDER')
        ]);

        $allowedOrder1 = Order::factory()->create([
            'status_code' => OrderStatus::getCodeByName('ORDER_DETAILS_EMAILED_TO_USER')
        ]);

        $allowedOrder2 = Order::factory()->create([
            'status_code' => OrderStatus::getCodeByName('BEING_EVALUATED_FOR_PURCHASE')
        ]);
        

        $this->assertFalse(OrderItemAndPurchaseItemAssociator::isOrderWithStatusAllowedForPurchaseAssociations($notAllowedOrder));
        $this->assertTrue(OrderItemAndPurchaseItemAssociator::isOrderWithStatusAllowedForPurchaseAssociations($allowedOrder1));
        $this->assertTrue(OrderItemAndPurchaseItemAssociator::isOrderWithStatusAllowedForPurchaseAssociations($allowedOrder2));
    }
}
