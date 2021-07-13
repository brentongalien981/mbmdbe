<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use App\Models\ProductSeller;
use App\Models\SizeAvailability;
use App\Bmd\Generals\GeneralHelper;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        // create parent cart only if there's no 1st cart record in db
        // otherwise, just reference the first cart.
        $cart = Cart::first();
        if (!$cart) {
            $cart = new Cart();
            $cart->stripe_payment_intent_id = 'BMD-FAKE-' . Str::random(10);
            $cart->is_active = 0;
            $cart->save();
        }



        return [
            'id' => 0, // Str::uuid()->toString(),
            'cart_id' => $cart->id,
            'stripe_payment_intent_id' => $cart->stripe_payment_intent_id,
            'status_code' => 8300, // BEING_EVALUATED_FOR_PURCHASE
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),

            'street' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'province' => $this->faker->state(),
            'country' => 'US',
            'postal_code' => $this->faker->postcode(),
            'phone' => '888-8888',
            'email' => $this->faker->email(),
            'charged_subtotal' => 0.0,
            'charged_shipping_fee' => 0.0,
            'charged_tax' => 0.0,
            'earliest_delivery_date' => now(),
            'latest_delivery_date' => now(),
            'projected_total_delivery_days' => 3,
            'created_at' => now(),
            'updated_at' => now()

        ];
    }



    public function configure()
    {
        return $this->afterCreating(function (Order $o) {

            // Create order-items.
            $numOfOrderItems = rand(1, 3);
            $alreadyUsedSizeAvailIds = []; 
            $allSizeAvailabilitiesCount = SizeAvailability::all()->count();

            for ($i = 0; $i < $numOfOrderItems; $i++) {

                $randSizeAvailId = rand(1, $allSizeAvailabilitiesCount);

                // We don't want repeated order-items with same size-avail-id.
                while (true) {
                    if (in_array($randSizeAvailId, $alreadyUsedSizeAvailIds)) {
                        $randSizeAvailId = rand(1, $allSizeAvailabilitiesCount);
                    } else {
                        $alreadyUsedSizeAvailIds[] = $randSizeAvailId;
                        break;
                    }
                }


                $randSizeAvail = SizeAvailability::find($randSizeAvailId);
    
                $sp = ProductSeller::find($randSizeAvail->seller_product_id);
    
                $oi = new OrderItem();
                $oi->order_id = $o->id;
                $oi->product_id = $sp->product_id;
                $oi->product_seller_id = $sp->id;
                $oi->size_availability_id = $randSizeAvail->id;
                $oi->price = $sp->sell_price;
                $oi->quantity = rand(1, 10);
                $oi->status_code = 300; // DEFAULT
                $oi->created_at = $o->created_at;
                $oi->save();
            }



            // Update the order's finance data with random amounts.
            $orderSubtotal = 0.0;

            foreach ($o->orderItems as $i) {
                $orderSubtotal += ($i->price * $i->quantity);
            }

            $o->charged_subtotal = $orderSubtotal;
            $o->charged_shipping_fee = $orderSubtotal * 0.10;
            $o->charged_tax = ($o->charged_subtotal + $o->charged_shipping_fee) * 0.13;
            $o->save();
        });
    }



    public static function generateFakeBmdOrders($date, $maxNumOrdersToCreate)
    {
        $numOfOrdersToCreate = rand(0, $maxNumOrdersToCreate);
        $numOfHoursInDay = 24;

        $numOfOrdersLeftToBeCreated = $numOfOrdersToCreate;

        for ($h = 0; $h < $numOfHoursInDay; $h++) {

            if ($numOfOrdersLeftToBeCreated == 0) {
                break;
            }

            $numOfOrdersToCreateThisHour = rand(0, $numOfOrdersLeftToBeCreated);
            $numOfOrdersLeftToBeCreated -= $numOfOrdersToCreateThisHour;

            $dateTime = $date . ' ' . $h . ':00:00';

            Order::factory()
                ->count($numOfOrdersToCreateThisHour)
                ->createWithUuid()
                // ->has(OrderItem::factory()->count(rand(1, 3))) BMD-DELETE
                ->create([
                    'created_at' => $dateTime,
                    'earliest_delivery_date' => GeneralHelper::getDateInStrWithData($date, 2),
                    'latest_delivery_date' => GeneralHelper::getDateInStrWithData($date, 3)
                ]);
        }
    }



    public function createWithUuid()
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => Str::uuid()->toString()
            ];
        });
    }



    public static function testRandomCreate()
    {
        return Order::factory()->createWithUuid()
            ->has(OrderItem::factory()->count(rand(1, 3)))
            ->create();



        // return Order::factory()->make([
        //     // 'id' => Str::uuid()->toString(),
        //     'earliest_delivery_date' => GeneralHelper::getDateInStrWithData('2021-02-16', 2),
        //     'latest_delivery_date' => GeneralHelper::getDateInStrWithData('2021-02-16', 3),
        //     'created_at' => '2021-02-16',
        //     'updated_at' => '2021-02-16'
        // ]);



        // Tinker codes.
        // $fPath = Database\Factories\OrderFactory::class
        // $o = $fPath::testRandomCreate()

        // $of = Database\Factories\OrderFactory::class
        // $d = '2021-06-01';
        // $of::generateFakeBmdOrders($d, 2)
    }
}
