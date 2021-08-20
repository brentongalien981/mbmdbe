<?php

namespace App\Http\Controllers;

use App\Bmd\Generals\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    private const NUM_OF_DISPLAYED_PURCHASES_PER_PAGE = 10;



    public function index(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Purchase::class);


        $purchasesQuery = $this->getPurchasesQuery($r);
        $totalNumOfPurchasesForQuery = $purchasesQuery->count();

        // BMD-TODO:
        // $numOfPurchasesToSkip = ($r->pageNum - 1) * self::NUM_OF_DISPLAYED_ORDERS_PER_PAGE;
        $numOfPurchasesToSkip = 0;

        $purchases = $purchasesQuery->skip($numOfPurchasesToSkip)
            ->take(self::NUM_OF_DISPLAYED_PURCHASES_PER_PAGE)
            ->get();

        $purchases = PurchaseResource::collection($purchases);


        return [
            'isResultOk' => true,
            'objs' => [
                'purchases' => $purchases,
                'paginationData' => [
                    'totalNumOfPurchasesForQuery' => $totalNumOfPurchasesForQuery
                ]
            ],
            // BMD-DELETE
            'requestData' => [
                'r->request' => GeneralHelper::jsonifyObj($r->request)
            ]
        ];
    }



    private function getPurchasesQuery(Request $r)
    {
        $idQueryPhrase = '%' . $r->id . '%';
        $sellerIdQueryPhrase = '%' . $r->sellerId . '%';

        $chargedSubtotalQueryPhrase = '%' . $r->chargedSubtotal . '%';
        $chargedShippingFeeQueryPhrase = '%' . $r->chargedShippingFee . '%';
        $chargedOtherFeeQueryPhrase = '%' . $r->chargedOtherFee . '%';
        $chargedTaxQueryPhrase = '%' . $r->chargedTax . '%';

        $statusCodeQueryPhrase = '%' . $r->statusCode . '%';
        $orderIdFromSellerSiteQueryPhrase = '%' . $r->orderIdFromSellerSite . '%';
        $shippingIdFromCarrierQueryPhrase = '%' . $r->shippingIdFromCarrier . '%';
        $notesQueryPhrase = '%' . $r->notes . '%';


        $purchasesQuery = Purchase::where('id', 'like', $idQueryPhrase)
            ->where('seller_id', 'like', $sellerIdQueryPhrase)
            ->where('status_code', 'like', $statusCodeQueryPhrase);

        if (trim($r->chargedSubtotal) != '') {
            $purchasesQuery = $purchasesQuery->where('charged_subtotal', 'like', $chargedSubtotalQueryPhrase);
        }
        if (trim($r->chargedShippingFee) != '') {
            $purchasesQuery = $purchasesQuery->where('charged_shipping_fee', 'like', $chargedShippingFeeQueryPhrase);
        }
        if (trim($r->chargedOtherFee) != '') {
            $purchasesQuery = $purchasesQuery->where('charged_other_fee', 'like', $chargedOtherFeeQueryPhrase);
        }
        if (trim($r->chargedTax) != '') {
            $purchasesQuery = $purchasesQuery->where('charged_tax', 'like', $chargedTaxQueryPhrase);
        }
        if (trim($r->orderIdFromSellerSite) != '') {
            $purchasesQuery = $purchasesQuery->where('order_id_from_seller_site', 'like', $orderIdFromSellerSiteQueryPhrase);
        }
        if (trim($r->shippingIdFromCarrier) != '') {
            $purchasesQuery = $purchasesQuery->where('shipping_id_from_carrier', 'like', $shippingIdFromCarrierQueryPhrase);
        }
        if (trim($r->notes) != '') {
            $purchasesQuery = $purchasesQuery->where('notes', 'like', $notesQueryPhrase);
        }


        $purchasesQuery = $purchasesQuery
            ->where('estimated_delivery_date', '>=', $r->estimatedDeliveryDate)
            ->orWhere('estimated_delivery_date', null)
            ->where('created_at', '>=', $r->createdAt)
            ->where('updated_at', '>=', $r->updatedAt)

            ->orderBy('created_at', 'desc');

        return $purchasesQuery;
    }
}
