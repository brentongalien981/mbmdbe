<?php

namespace App\Console\Commands;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\ProductSeller;
use App\Models\PurchaseStatus;
use App\Models\OrderItemStatus;
use Illuminate\Console\Command;
use App\Models\PurchaseItemStatus;

class PrepareBmdPurchasesCommand extends Command
{
    public const scheduledDispatchTime = '03:05';




    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'BmdPurchases:Prepare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepare BMD-Purchase-records.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $numOfSecInDay = 86400;
        $dateObjToday = getdate();
        $dateObjYesterday = getdate($dateObjToday[0] - $numOfSecInDay);

        $ordersStartDateInStr = $dateObjYesterday['year'] . '-' . $dateObjYesterday['mon'] . '-' . $dateObjYesterday['mday'];
        $ordersEndDateInStr = $dateObjYesterday['year'] . '-' . $dateObjYesterday['mon'] . '-' . $dateObjYesterday['mday'];


        $ordersYesterday = Order::where('created_at', '>=', $ordersStartDateInStr)
            ->where('created_at', '<=', $ordersEndDateInStr)
            ->get();

        foreach ($ordersYesterday as $o) {
            foreach ($o->orderItems as $oi) {

                $oiDefaultStatus = OrderItemStatus::where('name', OrderItemStatus::NAME_FOR_STATUS_DEFAULT)->get()[0];
                if ($oi->status_code != $oiDefaultStatus->code) {
                    continue;
                }

                $sellerProduct = ProductSeller::find($oi->product_seller_id);
                $seller = Seller::find($sellerProduct->seller_id);

                if ($this->doTodaysPurchasesAlreadyIncludeFromSeller($seller)) {
                    // TODO: update purchase-item
                } else {

                    // Create Purchase.
                    $p = new Purchase();
                    $p->seller_id = $seller->id;
                    $p->status_code = PurchaseStatus::where('name', PurchaseStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0]->code;
                    $p->save();


                    // Create PurchaseItem.
                    $pi = new PurchaseItem();
                    $pi->purchase_id = $p->id;
                    $pi->seller_product_id = $sellerProduct->id;
                    $pi->size_availability_id = $oi->size_availability_id;
                    $pi->quantity = $oi->quantity;
                    $pi->projected_price = $oi->price;
                    $pi->status_code = PurchaseItemStatus::where('name', PurchaseItemStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0]->code;
                    $pi->save();


                    $oi->status_code = OrderItemStatus::where('name', OrderItemStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0]->code;
                    $oi->save();


                    $ii = InventoryItem::where('seller_product_id', $sellerProduct->id)->get()[0];
                    $ii->to_be_purchased_quantity += $oi->quantity;

                }
            }
        }

        return 0;
    }



    private function doTodaysPurchasesAlreadyIncludeFromSeller($seller)
    {
        $todaysPurchases = Purchase::getTodaysPurchases();

        foreach ($todaysPurchases as $p) {
            if ($p->seller_id == $seller->id) {
                return true;
            }
        }

        return false;
    }
}
