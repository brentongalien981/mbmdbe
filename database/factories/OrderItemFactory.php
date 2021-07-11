<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\ProductSeller;
use App\Models\SizeAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $allSizeAvailabilitiesCount = SizeAvailability::all()->count();
        $randSizeAvailId = rand(1, $allSizeAvailabilitiesCount);
        $randSizeAvail = SizeAvailability::find($randSizeAvailId);

        $sp = ProductSeller::find($randSizeAvail->seller_product_id);


        return [
            'product_id' => $sp->product_id,
            'product_seller_id' => $sp->id,
            'size_availability_id' => $randSizeAvail->id,
            'price' => $sp->sell_price,
            'quantity' => rand(1, 3),
            'status_code' => 300 // DEFAULT
        ];
    }
}
