<?php

namespace App\Http\Controllers;

use App\Bmd\Generals\GeneralHelper2;
use Exception;
use App\Models\Order;
use EasyPost\EasyPost;
use App\Models\Dispatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Exceptions\BmdEpAddressException;
use App\Exceptions\CouldNotFindShipmentRatesException;
use App\Exceptions\NotAllowedOrderStatusForProcess;
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
        
        $isResultOk = false;


        // BMD-ON-ITER: Development, Staging
        EasyPost::setApiKey(env('EASYPOST_TK'));



        try {

            $entireProcessData['order'] = Order::findOrFail($r->orderId);            
            EpShipmentRecommender::guardForOrderStatus($entireProcessData['order']);

            $entireProcessData['originAddress'] = EpShipmentRecommender::setOriginAddress($entireProcessData);
            $entireProcessData['destinationAddress'] = EpShipmentRecommender::setDestinationAddress($entireProcessData);
            $entireProcessData['parcel'] = EpShipmentRecommender::setParcel($entireProcessData);
            $entireProcessData['shipment'] = EpShipmentRecommender::setShipment($entireProcessData);

            $entireProcessData['modifiedRateObjs'] = EpShipmentRecommender::getModifiedRateObjs($entireProcessData['shipment']->rates);
            $entireProcessData['efficientShipmentRates'] = EpShipmentRecommender::getEfficientShipmentRates($entireProcessData['modifiedRateObjs']);

            $entireProcessData['resultCode'] = GeneralHttpResponseCodes::OK;
            $isResultOk = true;
        } catch (Exception $e) {
            $entireProcessData['resultCode'] = $this->setErroneousBmdResultCodeForCheckPossibleShipping($e);
        }


        return [
            'isResultOk' => $isResultOk,
            'objs' => [
                'entireProcessData' => GeneralHelper2::pseudoJsonify($entireProcessData)
            ]
        ];
    }



    private function setErroneousBmdResultCodeForCheckPossibleShipping($e)
    {
        switch (get_class($e)) {
            case NotAllowedOrderStatusForProcess::class:
                return EpShipmentHttpResponseCodes::getNotAllowedOrderStatusForProcessException($e);
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
