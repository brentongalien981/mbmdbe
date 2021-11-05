<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
// use PHPUnit\Framework\TestCase;
use App\Models\Order;
use Illuminate\Support\Str;
use App\Bmd\Generals\GeneralHelper2;
use Database\Factories\OrderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;



    /** @test */
    public function it_saves_order_with_given_params()
    {
        $o = Order::factory()->make();
        $o = json_encode($o);
        $o = json_decode($o);
        $o = GeneralHelper2::pseudoJsonify($o);
        // dump($o);        

        $theOrder = Order::saveWithParams($o);

        $this->assertTrue(isset($theOrder->id));
        $this->assertEquals($o['email'], $theOrder->email);
        $this->assertDatabaseHas('orders', [
            'stripe_payment_intent_id' => $o['stripe_payment_intent_id'],
            'email' => $o['email']
        ]);
        $this->assertCount(1, Order::all());   
    }



    /** @test */
    public function it_updates_order_with_given_params()
    {
        $o = Order::factory()->create();
        $o = json_encode($o);
        $o = json_decode($o);
        $orderParams = GeneralHelper2::pseudoJsonify($o);

        $oldFirstName = $orderParams['first_name'];
        $this->assertDatabaseHas('orders', [
            'first_name' => $oldFirstName
        ]);


        $newFirstName = 'Bren';      
        $orderParams['first_name'] = $newFirstName;

        $theOrder = Order::saveWithParams($orderParams, 'update');

        $this->assertDatabaseMissing('orders', [
            'first_name' => $oldFirstName
        ]);
        $this->assertDatabaseHas('orders', [
            'first_name' => $newFirstName
        ]);


        $dbOrder = Order::find($orderParams['id']);
        $this->assertEquals($dbOrder->id, $theOrder->id);
        $this->assertCount(1, Order::all());
    }
}
