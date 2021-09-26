<?php

namespace App\Http\BmdHelpers;

use EasyPost\Parcel;
use EasyPost\Address;
use EasyPost\Shipment;
use App\Bmd\Constants\BmdGlobalConstants;
use App\Exceptions\BmdEpAddressException;
use App\Exceptions\CouldNotFindShipmentRatesException;
use App\Exceptions\NullBmdPredefinedPackageException;

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
}
