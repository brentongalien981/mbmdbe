<?php

namespace App\Http\Controllers;

use App\Exceptions\BmdEpAddressException;
use Exception;
use EasyPost\EasyPost;
use App\Models\Dispatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\BmdHelpers\EpShipmentRecommender;

class ShippingController extends Controller
{
    public function checkPossibleShipping(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('checkPossibleShipping', Dispatch::class);

        $entireProcessData = [
            'entireProcessComments' => [],
            'customErrors' => [],
            'bmdResultCode' => null,
            'shippingInfo' => null
        ];


        // BMD-ON-ITER: Development, Staging
        EasyPost::setApiKey(env('EASYPOST_TK'));



        try {

            $entireProcessData['originAddress'] = EpShipmentRecommender::setOriginAddress($entireProcessData);



            // Set EP-destination-address.
            //     if (!$this->validateDestinationAddress($entireProcessData)) {
            //         $entireProcessData['resultCode'] = self::INVALID_DESTINATION_COUNTRY_EXCEPTION['code'];
            //         throw new Exception(self::INVALID_DESTINATION_COUNTRY_EXCEPTION['name']);
            //     }
            //     $entireProcessData['destinationAddress'] = $this->setDestinationAddress($entireProcessData);


            // Set EP-parcel (predefined-UPS-package-size)
            //     $entireProcessData['parcel'] = $this->setParcel($entireProcessData);


            // Get EP-shipment (with rates)        
            //     $entireProcessData['packageInfo'] = $entireProcessData['packageInfo'];
            //     $shipmentObj = $this->setShipment($entireProcessData);


            //     // Check.
            //     if (!$this->doesShipmentHaveRates($shipmentObj)) {
            //         // Re-create the parcel & shipment.
            //         $usePredefinedPackageProp = false;
            //         $entireProcessData['parcel'] = $this->setParcel($entireProcessData, $usePredefinedPackageProp);

            //         $shipmentObj = $this->setShipment($entireProcessData);
            //     }


            //     // 2nd check.
            //     if (!$this->doesShipmentHaveRates($shipmentObj)) {
            //         $params['resultCode'] = self::COULD_NOT_FIND_SHIPMENT_RATES['code'];
            //         throw new Exception(self::COULD_NOT_FIND_SHIPMENT_RATES['name']);
            //     }


            //     $entireProcessData['shipment'] = $shipmentObj;
            //     $entireProcessData['parsedRateObjs'] = $this->getParsedRateObjs($entireProcessData['shipment']->rates);
            //     $entireProcessData['modifiedRateObjs'] = $this->getModifiedRateObjs($entireProcessData['parsedRateObjs']);
            //     $entireProcessData['efficientShipmentRates'] = $this->getEfficientShipmentRates($entireProcessData['modifiedRateObjs']);
            //     $entireProcessData['shipmentId'] = $entireProcessData['shipment']->id;

            //     $entireProcessData['resultCode'] = self::ENTIRE_PROCESS_OK['code'];
            //     $entireProcessData['entireProcessComments'][] = self::ENTIRE_PROCESS_OK['name'];
        } catch (Exception $e) {
            $this->setErroneousBmdResultCodeForCheckPossibleShipping($e, $entireProcessData);
        }


        // $entireProcessData['entireProcessComments'] = $entireProcessData['entireProcessComments'];


        return [
            'isResultOk' => true,
            'objs' => [
                'shippingInfo' => null,
            ]
        ];
    }



    private function setErroneousBmdResultCodeForCheckPossibleShipping($e, $entireProcessData)
    {
        switch (get_class($e)) {
            case BmdEpAddressException::class:
                // BMD-TODO: Set the CLASS: EpShipmentHttpResponseCodes
                break;
            
            default:
                break;
        }
    }
}
