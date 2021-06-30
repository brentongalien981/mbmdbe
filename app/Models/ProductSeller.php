<?php

namespace App\Models;

use App\Bmd\Constants\BmdGlobalConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSeller extends Model
{
    use HasFactory;


    protected $table = 'product_seller';



    public static function syncBmdSellerProductsSizeAvailabilityQuantitiesWithInventory() {
        $bmdSeller = Seller::where('name', BmdGlobalConstants::BMD_SELLER_NAME)->get()[0];

        $bmdSellerProducts = self::where('seller_id', $bmdSeller->id)->get();

        foreach ($bmdSellerProducts as $sp) {
            foreach ($sp->sizeAvailabilities as $sa) {
                $ii = InventoryItem::where('seller_product_id', $sp->id)->where('size_availability_id', $sa->id)->get()[0] ?? null;
                if ($ii) {
                    $ii->in_stock_quantity = $sa->quantity;
                    $ii->save();
                }
            }
        }
    }



    public static function resetSizeAvailabilityQuantitiesOfNonBmdSellerProducts() {
        $bmdSeller = Seller::where('name', BmdGlobalConstants::BMD_SELLER_NAME)->get()[0];

        $allNonBmdSellerProducts = self::whereNotIn('seller_id', [$bmdSeller->id])->get();

        foreach ($allNonBmdSellerProducts as $sp) {
            foreach ($sp->sizeAvailabilities as $sa) {
                $sa->quantity = $sa->daily_reset_quantity;
                $sa->save();
            }
        }
    }



    public function sizeAvailabilities()
    {
        return $this->hasMany(SizeAvailability::class, 'seller_product_id', 'id');
    }
}
