<?php

namespace Database\Factories;

use App\Models\Order;
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
        return [
            //
        ];
    }



    public static function generateFakeBmdOrders($date, $numOfOrdersToCreate)
    {
        $numOfHoursInDay = 24;
        $numOfOrdersLeftToBeCreated = $numOfOrdersToCreate;

        for ($h = 0; $h < $numOfHoursInDay; $h++) {
            
            if ($numOfOrdersLeftToBeCreated == 0) { break; }

            $numOfOrdersToCreateThisHour = rand(0, $numOfOrdersLeftToBeCreated);
            $numOfOrdersLeftToBeCreated -= $numOfOrdersToCreateThisHour;

            $dateTime = $date . ' ' . $h . ':00:00';

            Order::factory()->count($numOfOrdersToCreateThisHour)->make([
                // BMD-TODO: add other props needed
                'created_at' => $dateTime,
                'updated_at' => $dateTime
            ]);
        }
    }
}
