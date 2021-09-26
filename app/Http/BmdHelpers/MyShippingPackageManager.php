<?php

namespace App\Http\BmdHelpers;

use App\Models\Product;
use App\Models\PackageItemType;

class MyShippingPackageManager
{
    /**
     * BMD-ON-ITER: Staging, Deployment: Make sure the weightLimits, itemTypeLimits, dimensions are still up-to-date with the carriers.
     * weightLimit is in oz
     * dimensions are in inches
     * itemTypeLimits are in number of pieces
     */
    public static $predefinePackagesByCarrier = [
        'UPS' => [
            'UPSLetter' => [
                'weightLimit' => 16.00,
                'itemTypeLimits' => ['shirt' => 3.0, 'jersey' => 2.0, 'shorts' => 2.0],
                'dimensions' => ['length' => 15, 'width' => 9.5, 'height' => 1]
            ],
            'SmallExpressBox' => [
                'weightLimit' => 480.00,
                'itemTypeLimits' => ['shirt' => 10.0, 'jersey' => 10.0, 'shorts' => 6.0, 'hoodie' => 2.0],
                'dimensions' => ['length' => 13, 'width' => 11, 'height' => 2]
            ],
            'UPS10kgBox' => [
                'weightLimit' => 352.00,
                'itemTypeLimits' => ['shirt' => 35.0, 'jersey' => 30.0, 'shorts' => 20.0, 'hoodie' => 8.0, 'shoes' => 4.0],
                'dimensions' => ['length' => 16.5, 'width' => 13.25, 'height' => 10.75]
            ],
            'UPS25kgBox' => [
                'weightLimit' => 880.00,
                'itemTypeLimits' => ['shirt' => 50.0, 'jersey' => 42.0, 'shorts' => 30.0, 'hoodie' => 12.0, 'shoes' => 6.0, 'pctowercase' => 1.0],
                'dimensions' => ['length' => 19.75, 'width' => 17.75, 'height' => 13.25]
            ]
        ],
        'FedEx' => [],
        'DHL' => [],
        'CanadaPost' => []
    ];



    public static function getOrderTotalWeight($items)
    {

        $totalWeight = 0.0;
        foreach ($items as $i) {

            $i = json_decode($i);
            $p = Product::find($i->productId);
            $totalWeight += $p->weight * $i->quantity;
        }

        return $totalWeight;
    }



    /**
     * Sample use: 
     *      $testCartItems = [
     *          ['id' => 1, 'quantity' => 2, 'productId' => 1, 'itemTypeId' => 1],
     *          ['id' => 3, 'quantity' => 2, 'productId' => 1, 'itemTypeId' => 1]
     *      ];
     *      return self::getPackageInfo(json_encode($testCartItems));
     */
    public static function getPackageInfo($items)
    {

        $itemTypes = PackageItemType::orderByDesc('encompassing_level')->get();

        // The itemType of the order-item with the Highest Encompassing Level.
        $refItemType = null;

        $hasFoundRef = false;
        foreach ($itemTypes as $t) {

            foreach ($items as $i) {
                $i = json_decode($i);
                if ($t->id === $i->packageItemTypeId) {
                    $refItemType = $t;
                    $hasFoundRef = true;
                    break;
                }
            }

            if ($hasFoundRef) {
                break;
            }
        }



        /**
         * Figure out the total quantity of all the order-items in an imaginary unit based on the $refItemType.
         * ie) Convert 3 shirt to x hoodie quantity. The refItemType is hoodie.
         *      1) currentItemTotalConvertedQty = (ref-conversion-ratio) / (current-item-conversion-ratio) * (current-item-qty)
         *      2) x hoode = (12.00 hoodie) / (50.00 shirt) * (3 shirt)
         *      3) 0.72 hoodie
         *      4) 3 shirt = 0.72 hoodie
         */
        $allItemsTotalConvertedQty = 0.00;

        foreach ($items as $i) {

            $i = json_decode($i);
            $currentItemTotalConvertedQty = 0.00;
            $refConversionRatio = $refItemType->conversion_ratio;
            $currentItemType = PackageItemType::find($i->packageItemTypeId);
            $currentItemConversionRatio = $currentItemType->conversion_ratio;
            $currentItemQty = $i->quantity;

            $currentItemTotalConvertedQty = $refConversionRatio / $currentItemConversionRatio * $currentItemQty;
            $allItemsTotalConvertedQty += $currentItemTotalConvertedQty;
        }



        // Figure-out the cheapest predefined-package that can hold that amount of total-converted-qty.
        $orderTotalWeight = self::getOrderTotalWeight($items);
        $selectedPredefinedPackageName = null;
        $UpsPredefinePackages = self::$predefinePackagesByCarrier['UPS'];

        foreach ($UpsPredefinePackages as $ppName => $ppDetails) {

            $ppItemTypeLimits = $ppDetails['itemTypeLimits'];

            if (array_key_exists($refItemType->name, $ppItemTypeLimits)) {

                $ppMaxCapacityForItemType = $ppItemTypeLimits[$refItemType->name];
                if (
                    isset($ppMaxCapacityForItemType) &&
                    $ppMaxCapacityForItemType >= $allItemsTotalConvertedQty &&
                    $orderTotalWeight <= $ppDetails['weightLimit']
                ) {

                    $selectedPredefinedPackageName = $ppName;
                    break;
                }
            }
        }




        //
        if ($orderTotalWeight === 0.0 || !isset($selectedPredefinedPackageName)) {
            return null;
        }

        return [
            'convertedQty' => $allItemsTotalConvertedQty,
            'totalWeight' => $orderTotalWeight,
            'predefinedPackageName' => $selectedPredefinedPackageName,
            'dimensions' => self::$predefinePackagesByCarrier['UPS'][$selectedPredefinedPackageName]['dimensions']
        ];
    }



    /**
     * Extract reduced-order-items-data to formulate predefined-package-info.
     * Sample return: 
     *      $data = [
     *          ['id' => 1, 'quantity' => 2, 'productId' => 1, 'itemTypeId' => 1],
     *          ['id' => 3, 'quantity' => 2, 'productId' => 1, 'itemTypeId' => 1]
     *      ];
     * 
     *      return json_encode($data)
     * 
     */
    public static function extractReducedOrderItemsData($orderItems)
    {
        $reducedData = [];

        foreach ($orderItems as $oi) {
            $reducedData[] = [
                'id' => $oi->id,
                'productId' => $oi->product_id,
                'itemTypeId' => Product::find($oi->product_id)->package_item_type_id
            ]; 
        }

        return json_encode($reducedData);
    }
}
