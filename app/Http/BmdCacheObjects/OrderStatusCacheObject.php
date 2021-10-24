<?php

namespace App\Http\BmdCacheObjects;

use App\Models\OrderStatus;


class OrderStatusCacheObject extends BmdCacheObject
{
    protected $lifespanInMin = 1440;



    public static function getDataByName($name) {

        $cacheKey = 'orderStatus?name=' . $name;
        $orderStatusCO = new self($cacheKey);

        if (!isset($orderStatusCO->entireData) || !isset($orderStatusCO->data) || $orderStatusCO->shouldRefresh()) {
            $orderStatusCO->data = OrderStatus::where('name', $name)->get()[0] ?? null;
            $orderStatusCO->save();
        }

        return $orderStatusCO->data;
    }



    public static function getDataByCode($code) {

        $cacheKey = 'orderStatus?code=' . $code;
        $orderStatusCO = new self($cacheKey);

        if (!isset($orderStatusCO->entireData) || !isset($orderStatusCO->data) || $orderStatusCO->shouldRefresh()) {
            $orderStatusCO->data = OrderStatus::where('code', $code)->get()[0] ?? null;
            $orderStatusCO->save();
        }

        return $orderStatusCO->data;
    }



    public static function getCodeByName($name) {
        return self::getDataByName($name)->code;
    }



    public static function getReadableNameByName($name) {
        return self::getDataByName($name)->readable_name;
    }



    public static function getReadableNameByCode($code) {
        return self::getDataByCode($code)->readable_name;
    }

}