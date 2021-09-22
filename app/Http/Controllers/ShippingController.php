<?php

namespace App\Http\Controllers;

use EasyPost\EasyPost;
use App\Models\Dispatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;

class ShippingController extends Controller
{
    public function checkPossibleShipping(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('checkPossibleShipping', Dispatch::class);

        // BMD-ON-ITER: Staging
        EasyPost::setApiKey(env('EASYPOST_TK'));

        
        // BMD-TODO: Set EP-origin-address.


        // Set EP-destination-address.


        // Set EP-parcel (predefined-UPS-package-size)


        // Get EP-shipment (with rates)



        $entireProcessData = [
            'entireProcessComments' => [],
            'resultCode' => null,
            'shippingInfo' => null
        ];


        // try {

            // BMD-TODO: Create Specific BmdException class for the checking of address.
        //     if (!$this->validateDestinationAddress($entireProcessData)) {
        //         $entireProcessData['resultCode'] = self::INVALID_DESTINATION_COUNTRY_EXCEPTION['code'];
        //         throw new Exception(self::INVALID_DESTINATION_COUNTRY_EXCEPTION['name']);
        //     }

        //     $entireProcessData['originAddress'] = $this->setOriginAddress($entireProcessData);
        //     $entireProcessData['destinationAddress'] = $this->setDestinationAddress($entireProcessData);
        //     $entireProcessData['parcel'] = $this->setParcel($entireProcessData);
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
        // } catch (Exception $e) {
        //     if ($entireProcessData['resultCode'] === self::DEFAULT_INITIAL_RESULT['code']) {
        //         $entireProcessData['resultCode'] = self::CUSTOM_INTERNAL_EXCEPTION['code'];
        //     }
        //     $entireProcessData['entireProcessComments'][] = "caught exception ==> " . $e->getMessage();
        //     $entireProcessData['entireProcessComments'][] = "caught exception trace ==> " . $e->getTraceAsString();
        // }


        // $entireProcessData['entireProcessComments'] = $entireProcessData['entireProcessComments'];


        return [
            'isResultOk' => true,
            'objs' => [
                'shippingInfo' => null,
            ]
        ];
    }
}
