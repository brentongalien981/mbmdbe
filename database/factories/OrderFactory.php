<?php

namespace Database\Factories;

use App\Bmd\Generals\GeneralHelper;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Str;
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
            'id' => Str::uuid()->toString(),
            'cart_id' => $cart->id,
            'stripe_payment_intent_id' => $cart->stripe_payment_intent_id,
            'status_code' => 8015, // DELIVERED
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),

            'street' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'province' => $this->faker->state(),
            'country' => 'US',
            'postal_code' => $this->faker->postcode(),
            'phone' => $this->faker->phoneNumber(),
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
        return $this->afterMaking(function (Order $o) {
            // This is a workaround because doing the "inline-state-manipulation"
            // doesn't create a default id (UUID) right away, giving an error when 
            // saving to db. 
            $o->save();
        })->afterCreating(function (Order $user) {
            //
            // BMD-TODO: Create order-items.
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

            Order::factory()->count($numOfOrdersToCreateThisHour)->make([
                // BMD-TODO: add other props needed
                'created_at' => $dateTime,
                'updated_at' => $dateTime,
                'earliest_delivery_date' => GeneralHelper::getDateInStrWithData($date, 2),
                'latest_delivery_date' => GeneralHelper::getDateInStrWithData($date, 3)
            ]);
        }
    }



    public static function randomCreate()
    {
        // return Order::factory()->make();


        // BMD-TODO: add other props needed
        return Order::factory()->make([
            // 'id' => Str::uuid()->toString(),
            'earliest_delivery_date' => GeneralHelper::getDateInStrWithData('2021-02-16', 2),
            'latest_delivery_date' => GeneralHelper::getDateInStrWithData('2021-02-16', 3),
            'created_at' => '2021-02-16',
            'updated_at' => '2021-02-16'
        ]);



        // Tinker codes.
        // $fPath = Database\Factories\OrderFactory::class
        // $o = $fPath::randomCreate()
    }
}
