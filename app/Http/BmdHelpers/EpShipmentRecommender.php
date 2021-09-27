<?php

namespace App\Http\BmdHelpers;

use EasyPost\Parcel;
use EasyPost\Address;
use EasyPost\Shipment;
use App\Models\ShippingServiceLevel;
use App\Bmd\Constants\BmdGlobalConstants;
use App\Bmd\Generals\GeneralHelper2;
use App\Exceptions\BmdEpAddressException;
use App\Exceptions\NullBmdPredefinedPackageException;
use App\Exceptions\CouldNotFindShipmentRatesException;
use App\Exceptions\NotAllowedOrderStatusForProcess;
use App\Http\BmdCacheObjects\ShippingServiceLevelModelCollectionCacheObject;
use App\Models\OrderStatus;

class EpShipmentRecommender
{

    public static function setOriginAddress(&$entireProcessData)
    {
        $originAddressParams = [
            'verify' => [true],
            'name' => BmdGlobalConstants::COMPANY_INFO['owner_name'],
            'company' => BmdGlobalConstants::COMPANY_INFO['company'],
            'email' => BmdGlobalConstants::COMPANY_INFO['email'],
            'street1' => BmdGlobalConstants::COMPANY_INFO['street1'],
            'street2' => BmdGlobalConstants::COMPANY_INFO['street2'],
            'city' => BmdGlobalConstants::COMPANY_INFO['city'],
            'state' => BmdGlobalConstants::COMPANY_INFO['state'],
            'country' => BmdGlobalConstants::COMPANY_INFO['country'],
            'zip' => BmdGlobalConstants::COMPANY_INFO['zip'],
            'phone' => BmdGlobalConstants::COMPANY_INFO['phone'],
        ];

        $originAddress = Address::create($originAddressParams);


        $isAddressValid = $originAddress->verifications->delivery->success;

        if (!$isAddressValid) {

            $returnedAddressErrors = $originAddress->verifications->delivery->errors;

            $originAddressErrors = [];

            // Parse each error.
            foreach ($returnedAddressErrors as $e) {

                $ithErrorObj = [];
                foreach ($e as $eField => $eVal) {
                    $ithErrorObj[$eField] = $eVal;
                }

                $originAddressErrors[] = $ithErrorObj;
            }


            $entireProcessData['customErrors']['originAddressErrors'] = $originAddressErrors;

            throw new BmdEpAddressException();
        }


        return $originAddress;
    }



    public static function setDestinationAddress(&$entireProcessData)
    {
        $o = $entireProcessData['order'];

        $destinationAddressParams = [
            'verify' => [true],
            'name' => $o->first_name . " " . $o->last_name,
            'email' => $o->email,
            'phone' => $o->phone,
            'street1' => $o->street,
            'city' => $o->city,
            'state' => $o->province,
            'country' => $o->country,
            'zip' => $o->postal_code
        ];


        $destinationAddress = Address::create($destinationAddressParams);

        $isDestinationAddressValid = $destinationAddress->verifications->delivery->success;

        if (!$isDestinationAddressValid) {

            $destinationAddressVerificationErrors = $destinationAddress->verifications->delivery->errors;

            $destinationAddressErrors = [];
            foreach ($destinationAddressVerificationErrors as $e) {

                $ithErrorObj = [];
                foreach ($e as $field => $val) {
                    $ithErrorObj[$field] = $val;
                }

                $destinationAddressErrors[] = $ithErrorObj;
            }

            $entireProcessData['customErrors']['destinationAddressErrors'] = $destinationAddressErrors;

            throw new BmdEpAddressException();
        }

        return $destinationAddress;
    }



    public static function setParcel(&$entireProcessData)
    {
        $packageInfo = $entireProcessData['packageInfo'];

        if (!isset($packageInfo)) {

            $orderItems = $entireProcessData['order']->orderItems;
            $entireProcessData['reducedOrderItemsData'] = MyShippingPackageManager::extractReducedOrderItemsData($orderItems);
            $packageInfo = MyShippingPackageManager::getPackageInfo($entireProcessData['reducedOrderItemsData']);

            if (!isset($packageInfo)) {
                throw new NullBmdPredefinedPackageException();
            }
        }


        $parcel = null;
        if ($entireProcessData['shouldUsePredefinedPackageProp']) {

            $parcel = Parcel::create([
                "predefined_package" => $packageInfo['predefinedPackageName'],
                "weight" => $packageInfo['totalWeight']
            ]);

            $entireProcessData['entireProcessComments'][] = 'Parcel-obj created using prop: predefined_package.';
        } else {

            // NOTE: I'm doing this because EasyPost doesn't seem to return a shipment-obj with rates
            // sometimes if the parcel-obj has the property "predefined_package" when created.
            $parcel = Parcel::create([
                "length" => $packageInfo['dimensions']['length'],
                "width" => $packageInfo['dimensions']['width'],
                "height" => $packageInfo['dimensions']['height'],
                "weight" => $packageInfo['totalWeight']
            ]);

            $entireProcessData['entireProcessComments'][] = 'Parcel-obj created using package-info-dimensions.';
        }


        $entireProcessData['packageInfo'] = $packageInfo;
        return $parcel;
    }



    public static function setShipment(&$entireProcessData)
    {
        $shipment = self::setEpShipment($entireProcessData);

        // Check.
        if (!self::doesShipmentHaveRates($shipment)) {
            // Re-create the parcel & shipment, but this time without using UPS's PredefinePackageNames.
            $entireProcessData['shouldUsePredefinedPackageProp'] = false;
            $entireProcessData['parcel'] = self::setParcel($entireProcessData);

            $shipment = self::setEpShipment($entireProcessData);


            // 2nd check.
            if (!self::doesShipmentHaveRates($shipment)) {
                throw new CouldNotFindShipmentRatesException();
            }
        }

        return $shipment;
    }



    private static function setEpShipment($entireProcessData)
    {

        return Shipment::create([
            "to_address" => $entireProcessData['destinationAddress'],
            "from_address" => $entireProcessData['originAddress'],
            "parcel" => $entireProcessData['parcel']
        ]);
    }



    private static function doesShipmentHaveRates($shipment)
    {
        if (!isset($shipment->rates) || count($shipment->rates) == 0) {
            return false;
        }
        return true;
    }



    /**
     * For each rate, add value to field "delivery_days" if the retrieved rate has null.
     *
     * @param [] $parsedRateObjs
     * @return []
     */
    public static function getModifiedRateObjs($epShipmentRates)
    {
        $modifiedRateObjs = [];
        $shippingServiceLevels = ShippingServiceLevelModelCollectionCacheObject::getUpdatedModelCollection()->data;

        foreach ($epShipmentRates as $r) {

            if ($r->carrier != "UPS") {
                continue;
            }

            $aModifiedRateObj = GeneralHelper2::pseudoJsonify($r);
            if (!isset($r->delivery_days)) {
                $deliveryDays = ShippingServiceLevel::findDeliveryDaysForService($r->service, $shippingServiceLevels);

                if ($deliveryDays == 0) {
                    continue;
                }

                $aModifiedRateObj['delivery_days'] = $deliveryDays;
            }

            $modifiedRateObjs[] = $aModifiedRateObj;
        }

        return $modifiedRateObjs;
    }



    public static function getEfficientShipmentRates($modifiedRateObjs)
    {
        // Get the cheapest of all rates.
        $cheapestWithFastestRate = null;
        $cheapestRate = 1000000.0;
        $fastestDeliveryDays = 365;
        foreach ($modifiedRateObjs as $r) {
            if ((floatval($r['rate']) < $cheapestRate) ||
                (floatval($r['rate']) == $cheapestRate && $r['delivery_days'] < $fastestDeliveryDays)
            ) {
                $cheapestRate = floatval($r['rate']);
                $fastestDeliveryDays = $r['delivery_days'];
                $cheapestWithFastestRate = $r;
            }
        }


        // Get the fastest rate that has the cheapest.
        $fastestWithCheapestRate = null;
        $cheapestRate = 1000000.0;
        $fastestDeliveryDays = 365;
        foreach ($modifiedRateObjs as $r) {
            if (($r['delivery_days'] < $fastestDeliveryDays) ||
                ($r['delivery_days'] == $fastestDeliveryDays && floatval($r['rate']) < $cheapestRate)
            ) {
                $cheapestRate = floatval($r['rate']);
                $fastestDeliveryDays = $r['delivery_days'];
                $fastestWithCheapestRate = $r;
            }
        }


        $efficientShipmentRates = null;
        if ($cheapestWithFastestRate['id'] == $fastestWithCheapestRate['id']) {
            $efficientShipmentRates = [$cheapestWithFastestRate];
        } else {
            $efficientShipmentRates = [$cheapestWithFastestRate, $fastestWithCheapestRate];
        }

        return $efficientShipmentRates;
    }



    /**
     * Validate: Only allow order with status "TO_BE_PACKAGED", "BEING_PACKAGED".
     */
    public static function guardForOrderStatus($order)
    {
        $allowedStatuses[] = OrderStatus::getCodeByName('TO_BE_PACKAGED');
        $allowedStatuses[] = OrderStatus::getCodeByName('BEING_PACKAGED');

        if (!in_array($order->status_code, $allowedStatuses)) { 
            throw new NotAllowedOrderStatusForProcess();
        }        
    }
}
