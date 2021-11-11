<?php

namespace App\Http\Controllers;

use Exception;
use EasyPost\Rate;
use App\Models\Order;
use EasyPost\EasyPost;
use EasyPost\Shipment;
use App\Models\Dispatch;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use App\Bmd\Generals\GeneralHelper2;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\OrderResource;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\Resources\DispatchResource;
use App\Exceptions\BmdEpAddressException;
use App\Http\BmdHelpers\EpShipmentRecommender;
use App\Exceptions\NotAllowedOrderStatusForProcess;
use App\Exceptions\NullBmdPredefinedPackageException;
use App\Exceptions\CouldNotFindShipmentRatesException;
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
            'shouldUsePredefinedPackageProp' => true,
            'modifiedRateObjs' => null,
            'efficientShipmentRates' => null,
        ];

        $isResultOk = false;

        GeneralHelper2::setEasyPostApiKey();



        try {

            $entireProcessData['order'] = Order::findOrFail($r->orderId);
            EpShipmentRecommender::guardForOrderStatus($entireProcessData['order']);
            EpShipmentRecommender::guardForAlreadyExistingShipment($entireProcessData['order']);

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


        $allProcessData = GeneralHelper2::pseudoJsonify($entireProcessData);

        return [
            'isResultOk' => $isResultOk,
            'objs' => [
                'resultCode' => $allProcessData['resultCode'],
                'modifiedRateObjs' => $allProcessData['modifiedRateObjs'],
                'efficientShipmentRates' => $allProcessData['efficientShipmentRates'],
                'epShipmentId' => $allProcessData['shipment']['id'] ?? '',
                // BMD-ON-ITER: Staging, Deployment: Only return non-sensitive data.
                'allProcessData' => $allProcessData
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



    public function buyShippingLabel(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Dispatch::class);

        $isResultOk = false;

        $entireProcessData = [
            'comments' => [],
            'resultCode' => null,
            'order' => null,
            'epShipment' => null
        ];


        try {

            GeneralHelper2::setEasyPostApiKey();

            // Reference order.
            $entireProcessData['order'] = Order::findOrFail($r->orderId);
            EpShipmentRecommender::guardForAlreadyExistingShipment($entireProcessData['order']);

            // Retrieve EP-shipment.
            $entireProcessData['epShipment'] = Shipment::retrieve($r->probableShippingId);

            // Retrieve EP-shipment-rate.
            $epShipmentRate = Rate::retrieve($r->selectedShippingRateId);

            // Buy EP-shipment.
            $entireProcessData['epShipment']->buy(['rate' => $epShipmentRate]);

            // Update order's status to "SHIPPING_LABEL_BOUGHT", and ep-shipment-id.
            $entireProcessData['order']->ep_shipment_id = $entireProcessData['epShipment']->id;
            $entireProcessData['order']->status_code = OrderStatus::getCodeByName('SHIPPING_LABEL_BOUGHT');
            $entireProcessData['order']->save();

            $isResultOk = true;
            $entireProcessData['resultCode'] = GeneralHttpResponseCodes::OK;

        } catch (Exception $e) {

            $entireProcessData['resultCode'] = GeneralHttpResponseCodes::getGeneralExceptionCode($e);
        }


        return [
            'isResultOk' => $isResultOk,
            'objs' => [
                'order' => new OrderResource($entireProcessData['order']) ?? [],
                'epShipment' => GeneralHelper2::pseudoJsonify($entireProcessData['epShipment']),
                'dispatches' => DispatchResource::collection(Dispatch::getAvailableDispatches()),
                'resultCode' => GeneralHelper2::pseudoJsonify($entireProcessData['resultCode'])
            ]
        ];
    }
}
