<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Order;
use App\Bmd\Generals\GeneralHelper2;
// use App\Providers\BmdAuthProvider;
use Database\Seeders\OrderStatusSeeder;
use App\Http\BmdHelpers\BmdAuthProvider;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;



    protected function setUp(): void
    {
        parent::setUp();
        $this->setRandomUserManagerUser();
        $this->setSampleOrderManager();
        $this->seed(OrderStatusSeeder::class);
    }



    /** @test */
    public function it_stores_an_order()
    {
        // $this->withoutExceptionHandling();       

        $orderData = Order::factory()->make();
        $orderData = json_encode($orderData);
        $orderData = json_decode($orderData);
        $orderData = GeneralHelper2::pseudoJsonify($orderData);

        $requestAuthData = [
            'bmdToken' => $this->sampleOrderManagerBmdAuth->token,
            'authProviderId' => $this->sampleOrderManagerBmdAuth->auth_provider_type_id,
        ];
        $requestData = array_merge($requestAuthData, $orderData);

        $response = $this->postJson('/api/orders/store', $requestData);

        $response->assertStatus(200)
            ->assertJson([
                'objs' => [
                    'order' => [
                        'email' => $requestData['email'],
                        'last_name' => $requestData['last_name'],
                    ]
                ]
            ]);

        $savedOrder = Order::first();

        $this->assertEquals($orderData['email'], $savedOrder->email);
        $this->assertEquals($orderData['street'], $savedOrder->street);
    }



    /** @test */
    public function it_responds_403_unauthorized_when_non_order_manager_stores_order()
    {
        // $this->withoutExceptionHandling();    

        BmdAuthProvider::_forTestingOnly_unsetInstance();


        $response = $this->postJson('/api/orders/store', [
            'bmdToken' => $this->sampleUserManagerBmdAuth->token,
            'authProviderId' => $this->sampleUserManagerBmdAuth->auth_provider_type_id,
        ]);


        $response->assertStatus(403);
    }



    /** @test */
    public function it_reads_orders()
    {
        BmdAuthProvider::_forTestingOnly_unsetInstance();

        $orders = Order::factory()->count(20)->create();


        $requestAuthData = [
            'bmdToken' => $this->sampleOrderManagerBmdAuth->token,
            'authProviderId' => $this->sampleOrderManagerBmdAuth->auth_provider_type_id,
        ];

        $queryData = $this->getDefaultOrderProps();


        $requestData = array_merge($requestAuthData, $queryData);

        $response = $this->json('get', '/api/orders', $requestData);


        $response->assertStatus(200)
            ->assertJson([
                'isResultOk' => true
            ]);

        $this->assertEquals(10, count($response['objs']['orders']));
        $this->assertEquals(20, $response['objs']['paginationData']['totalNumOfProductsForQuery']);
    }



    /** @test */
    public function it_still_reads_ok_even_without_orders()
    {
        BmdAuthProvider::_forTestingOnly_unsetInstance();

        $requestAuthData = [
            'bmdToken' => $this->sampleOrderManagerBmdAuth->token,
            'authProviderId' => $this->sampleOrderManagerBmdAuth->auth_provider_type_id,
        ];

        $queryData = $this->getDefaultOrderProps();


        $requestData = array_merge($requestAuthData, $queryData);

        $response = $this->json('get', '/api/orders', $requestData);


        $response->assertStatus(200)
            ->assertJson([
                'isResultOk' => true
            ]);

        $this->assertEquals(0, count($response['objs']['orders']));
        $this->assertEquals(0, $response['objs']['paginationData']['totalNumOfProductsForQuery']);
    }



    /** @test */
    public function it_reads_orders_based_on_page_number()
    {
        BmdAuthProvider::_forTestingOnly_unsetInstance();

        $orders = Order::factory()->count(13)->create();

        $requestAuthData = [
            'bmdToken' => $this->sampleOrderManagerBmdAuth->token,
            'authProviderId' => $this->sampleOrderManagerBmdAuth->auth_provider_type_id,
        ];

        $queryData = $this->getDefaultOrderProps();


        $requestData = array_merge($requestAuthData, $queryData, ['pageNum' => 2]);

        $response = $this->json('get', '/api/orders', $requestData);


        $response->assertStatus(200)
            ->assertJson([
                'isResultOk' => true
            ]);

        $this->assertEquals(3, count($response['objs']['orders']));
        $this->assertEquals(13, $response['objs']['paginationData']['totalNumOfProductsForQuery']);
    }



    /** @test */
    public function it_reads_orders_with_filter_params()
    {
        BmdAuthProvider::_forTestingOnly_unsetInstance();

        $orders = Order::factory()->count(15)->create();
        $orders2 = Order::factory()->count(13)->create([
            'status_code' => 8100
        ]);

        $requestAuthData = [
            'bmdToken' => $this->sampleOrderManagerBmdAuth->token,
            'authProviderId' => $this->sampleOrderManagerBmdAuth->auth_provider_type_id,
        ];

        $queryData = $this->getDefaultOrderProps();
        $queryData['statusFilter'] = 8100;


        $requestData = array_merge($requestAuthData, $queryData, ['pageNum' => 2]);

        $response = $this->json('get', '/api/orders', $requestData);


        $response->assertStatus(200)
            ->assertJson([
                'isResultOk' => true
            ]);

        $this->assertEquals(28, Order::all()->count());
        $this->assertEquals(3, count($response['objs']['orders']));
        $this->assertEquals(13, $response['objs']['paginationData']['totalNumOfProductsForQuery']);
    }



    /** @test */
    public function it_shows_an_order()
    {
        BmdAuthProvider::_forTestingOnly_unsetInstance();

        $order = Order::factory()->create();

        $response = $this->json('get', '/api/orders/show', [
            'bmdToken' => $this->sampleOrderManagerBmdAuth->token,
            'authProviderId' => $this->sampleOrderManagerBmdAuth->auth_provider_type_id,
            'orderId' => $order->id
        ]);


        $response->assertStatus(200)
            ->assertJson([
                'isResultOk' => true
            ]);

        $this->assertEquals($order->id, $response['objs']['order']['id']);
        $this->assertEquals(1, Order::all()->count());
    }



    private function getDefaultOrderProps()
    {
        return [
            'orderIdFilter' => '',
            'userIdFilter' => '',
            'stripePaymentIntentIdFilter' => '',
            'firstNameFilter' => '',
            'lastNameFilter' => '',
            'phoneFilter' => '',
            'emailFilter' => '',
            'streetFilter' => '',
            'cityFilter' => '',
            'provinceFilter' => '',
            'countryFilter' => '',
            'postalCodeFilter' => '',
            'statusFilter' => '',
            'deliveryDaysFilter' => '',
            'earlyDeliveryDateFilter' => '2020-01-01',
            'lateDeliveryDateFilter' => '2020-01-01',
            'createDateFilter' => '2020-01-01',
            'updateDateFilter' => '2021-01-01'
        ];
    }
}
