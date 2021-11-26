<?php

namespace Tests\Unit;

use App\Models\Seller;
use App\Models\Product;
use App\Models\InventoryItem;
use App\Models\PackageItemType;
use App\Models\ProductSeller;
use App\Models\SizeAvailability;
use Database\Seeders\BrandSeeder;
use Database\Seeders\PackageItemTypeSeeder;
use Database\Seeders\SellerSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase as TestsTestCase;

class ProductSellerTest extends TestsTestCase
{
    use RefreshDatabase;



    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            BrandSeeder::class,
            TeamSeeder::class,
            ProductSeeder::class,
            SellerSeeder::class,
            PackageItemTypeSeeder::class,
        ]);
    }



    /** @test */
    public function it_syncs_bmd_seller_products_size_availability_quantities_with_inventory()
    {
        // Given bmd-seller-products (SPs) and their size-availabilities (SAs),
        // and inventory-items of the same FK ids and table-column value "in_stock_quantity" as them...
        $products = Product::factory()->count(10)->create();
        $bmdSellerId = Seller::where('name', env('COMPANY_SELLER_NAME'))->get()[0]->id;

        foreach ($products as $p) {
            $sp = ProductSeller::create(['product_id' => $p->id, 'seller_id' => $bmdSellerId, 'buy_price' => 0.49, 'sell_price' => 0.99, 'restock_days' => 1, 'link' => 'xxx']);
            $this->setSizeAvailabilitiesForSellerProduct($sp, $p);
        }



        // When I simulate buying or selling (increasing / decreasing) of SPs,
        $sizeAvailabilityQuantities = $this->simulateBuyOrSellForProductSizeAvailabilityQuantities();

        // and invoke the method "syncBmdSellerProductsSizeAvailabilityQuantitiesWithInventory()"...
        ProductSeller::syncBmdSellerProductsSizeAvailabilityQuantitiesWithInventory();

        $syncedSizeAvailabilities = $this->getSyncedSizeAvailabilities();

        // print_r($sizeAvailabilityQuantities);
        // print_r($syncedSizeAvailabilities);



        // Then the SPs SAs and IIs column-value should equal.
        $sps = ProductSeller::where('seller_id', $bmdSellerId)->get();
        foreach ($sps as $sp) {
            foreach ($sp->sizeAvailabilities as $sa) {
                $ii = InventoryItem::where('seller_product_id', $sp->id)->where('size_availability_id', $sa->id)->get()[0];
                $this->assertTrue($ii->in_stock_quantity == $sa->quantity);
            }
        }


        foreach ($syncedSizeAvailabilities as $ssa) {
            // sample sizeAvailabilityQuantities = ['SP-Id' => $sp->id, 'SA-Id' => $sa->id, 'size' => $sa->size, 'quantity' => $sa->quantity, 'reset-quantity' => $sa->daily_reset_quantity];
            $sa = SizeAvailability::find($ssa['SA-Id']);
            $sp = ProductSeller::find($ssa['SP-Id']);
            $ii = InventoryItem::where('seller_product_id', $sp->id)->where('size_availability_id', $sa->id)->get()[0];

            $this->assertEquals($ssa['quantity'], $sa->quantity);
            $this->assertEquals($sa->quantity, $ii->in_stock_quantity);
        }
    }



    /** @test */
    public function it_resets_size_availability_quantities_of_non_bmd_seller_products()
    {
        // Given non-bmd-seller-products (SPs) and their size-availabilities (SAs),
        // and inventory-items of the same FK ids and table-column value "in_stock_quantity" as them...
        $products = Product::factory()->count(30)->create();
        $bmdSellerId = Seller::where('name', env('COMPANY_SELLER_NAME'))->get()[0]->id;
        $numOfSellers = Seller::all()->count();


        foreach ($products as $p) {
            $randomSellerId = rand(1, $numOfSellers);
            $sp = ProductSeller::create(['product_id' => $p->id, 'seller_id' => $randomSellerId, 'buy_price' => 0.49, 'sell_price' => 0.99, 'restock_days' => 1, 'link' => 'xxx']);
            $this->setSizeAvailabilitiesForSellerProduct($sp, $p);
        }


        // When I simulate buying or selling (increasing / decreasing) of SPs,
        $sizeAvailabilityQuantities = $this->simulateBuyOrSellForProductSizeAvailabilityQuantities(false);

        // and invoke the method "syncBmdSellerProductsSizeAvailabilityQuantitiesWithInventory()"...
        ProductSeller::resetSizeAvailabilityQuantitiesOfNonBmdSellerProducts();

        $syncedSizeAvailabilities = $this->getSyncedSizeAvailabilities(false);

        

        // Then the SPs SAs and IIs column-value should equal.
        $count = 0;
        foreach ($syncedSizeAvailabilities as $ssa) {
            // sample sizeAvailabilityQuantities = ['SP-Id' => $sp->id, 'SA-Id' => $sa->id, 'size' => $sa->size, 'quantity' => $sa->quantity, 'reset-quantity' => $sa->daily_reset_quantity];
            $sa = SizeAvailability::find($ssa['SA-Id']);
            $sp = ProductSeller::find($ssa['SP-Id']);

            $this->assertEquals($ssa['quantity'], $sa->quantity);
            $this->assertEquals($sa->quantity, $sa->daily_reset_quantity);
            ++$count;
        }

        $this->assertEquals(count($syncedSizeAvailabilities), $count);

    }



    /** Helper Funcs */

    /**
     * @param $p Product
     */
    private function setSizeAvailabilitiesForSellerProduct($sp, $p)
    {
        $clothingSizes = ['S', 'M', 'L', 'XL', '2XL', '3XL'];
        $shoeSizes = ['6.0', '6.5', '7.0', '7.5', '8.0', '8.5', '9.0', '9.5', '10.0', '10.5', '11.0', '11.5', '12.0', '12.5', '13.0', '14.0'];
        $chosenSizes = [];

        if ($p->package_item_type_id == 5) { // shoes
            $chosenSizes = $shoeSizes;
        } else {
            $chosenSizes = $clothingSizes;
        }


        foreach ($chosenSizes as $s) {
            $initQuantity = rand(0, 20);
            $sa = SizeAvailability::create(['seller_product_id' => $sp->id, 'size' => $s, 'quantity' => $initQuantity, 'daily_reset_quantity' => $initQuantity]);
            InventoryItem::create(['product_id' => $p->id, 'seller_id' => $sp->seller_id, 'seller_product_id' => $sp->id, 'size_availability_id' => $sa->id, 'in_stock_quantity' => $initQuantity]);
        }
    }



    private function simulateBuyOrSellForProductSizeAvailabilityQuantities($forBmdSellerOnly = true)
    {        
        $sellerProducts = ProductSeller::all();

        if ($forBmdSellerOnly) {
            $bmdSellerId = Seller::where('name', env('COMPANY_SELLER_NAME'))->get()[0]->id;
            $sellerProducts = ProductSeller::where('seller_id', $bmdSellerId)->get();
        }


        $sizeAvailabilityQuantities = [];

        foreach ($sellerProducts as $sp) {
            foreach ($sp->sizeAvailabilities as $sa) {
                $sa->quantity = rand(0, $sa->quantity);
                $sa->save();
                $sizeAvailabilityQuantities[] = ['SP-Id' => $sp->id, 'SA-Id' => $sa->id, 'size' => $sa->size, 'quantity' => $sa->quantity, 'reset-quantity' => $sa->daily_reset_quantity];
            }
        }

        return $sizeAvailabilityQuantities;
    }



    private function getSyncedSizeAvailabilities($forBmdSellerOnly = true)
    {
        $bmdSellerId = Seller::where('name', env('COMPANY_SELLER_NAME'))->get()[0]->id;
        $sellerProducts = ProductSeller::whereNotIn('seller_id', [$bmdSellerId])->get();

        if ($forBmdSellerOnly) {
            $sellerProducts = ProductSeller::where('seller_id', $bmdSellerId)->get();
        }

        $sizeAvailabilityQuantities = [];

        foreach ($sellerProducts as $sp) {
            foreach ($sp->sizeAvailabilities as $sa) {
                $sizeAvailabilityQuantities[] = ['SP-Id' => $sp->id, 'SA-Id' => $sa->id, 'size' => $sa->size, 'quantity' => $sa->quantity, 'reset-quantity' => $sa->daily_reset_quantity];
            }
        }

        return $sizeAvailabilityQuantities;
    }
}
