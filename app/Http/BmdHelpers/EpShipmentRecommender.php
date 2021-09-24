<?php

namespace App\Http\BmdHelpers;

use EasyPost\Address;
use App\Bmd\Constants\BmdGlobalConstants;
use App\Exceptions\BmdEpAddressException;

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


        //
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
}
