<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Order;
use EasyPost\EasyPost;
use App\Models\Dispatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Exceptions\BmdEpAddressException;
use App\Exceptions\CouldNotFindShipmentRatesException;
use App\Http\BmdHelpers\EpShipmentRecommender;
use App\Exceptions\NullBmdPredefinedPackageException;
use App\Http\BmdHttpResponseCodes\GeneralHttpResponseCodes;
use App\Http\BmdHttpResponseCodes\EpShipmentHttpResponseCodes;

class ShippingController extends Controller
{
    public function checkPossibleShipping(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('checkPossibleShipping', Dispatch::class);

        $entireProcessData = [
            'entireProcessComments' => [],
            'customErrors' => [],
            'resultCode' => null,
            'shippingInfo' => null,
            'order' => null,
            'packageInfo' => null,
            'reducedOrderItemsData' => [],
            'shouldUsePredefinedPackageProp' => true
        ];


        // BMD-ON-ITER: Development, Staging
        EasyPost::setApiKey(env('EASYPOST_TK'));



        try {

            $entireProcessData['order'] = Order::findOrFail($r->orderId);
            $entireProcessData['originAddress'] = EpShipmentRecommender::setOriginAddress($entireProcessData);
            $entireProcessData['destinationAddress'] = EpShipmentRecommender::setDestinationAddress($entireProcessData);
            $entireProcessData['parcel'] = EpShipmentRecommender::setParcel($entireProcessData);
            $entireProcessData['shipment'] = EpShipmentRecommender::setShipment($entireProcessData);

            
            //     $entireProcessData['parsedRateObjs'] = $this->getParsedRateObjs($entireProcessData['shipment']->rates);
            //     $entireProcessData['modifiedRateObjs'] = $this->getModifiedRateObjs($entireProcessData['parsedRateObjs']);
            //     $entireProcessData['efficientShipmentRates'] = $this->getEfficientShipmentRates($entireProcessData['modifiedRateObjs']);
            //     $entireProcessData['shipmentId'] = $entireProcessData['shipment']->id;

            //     $entireProcessData['resultCode'] = self::ENTIRE_PROCESS_OK['code'];
            //     $entireProcessData['entireProcessComments'][] = self::ENTIRE_PROCESS_OK['name'];
        } catch (Exception $e) {
            $entireProcessData['resultCode'] = $this->setErroneousBmdResultCodeForCheckPossibleShipping($e);
        }


        // $entireProcessData['entireProcessComments'] = $entireProcessData['entireProcessComments'];


        return [
            'isResultOk' => true,
            'objs' => [
                'shippingInfo' => null,
            ]
        ];
    }



    private function setErroneousBmdResultCodeForCheckPossibleShipping($e)
    {
        switch (get_class($e)) {
            case BmdEpAddressException::class:
                return EpShipmentHttpResponseCodes::getFormattedBmdEpAddressException($e);
            case NullBmdPredefinedPackageException::class:
                return EpShipmentHttpResponseCodes::getNullBmdPredefinedPackageExceptionWithTrace($e);
            case CouldNotFindShipmentRatesException::class:
                return EpShipmentHttpResponseCodes::getCouldNotFindShipmentRatesExceptionWithTrace($e);
            default:
                return GeneralHttpResponseCodes::getGeneralExceptionCode($e);
        }
    }
}
