<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * BMD-ON-ITER: Staging, Deployment: Update TABLE "shipping_service_levels" to have
 * values in accordance to UPS records.
 */
class ShippingServiceLevel extends Model
{
    use HasFactory;



    public static function findDeliveryDaysForService($serviceName, $shippingServiceLevels)
    {
        foreach ($shippingServiceLevels as $l) {
            if ($l->name == $serviceName) {
                return $l->latest_delivery_days;
            }
        }

        return 0;
    }



    public static function deleteThis()
    {
        $a = 1;
        $b = 2;
        $c = 3;

        echo $a + $b + $c;
    }
}
