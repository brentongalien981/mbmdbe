<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;


    public static function getTodaysPurchases() {
        $dateObjToday = getdate();

        $dateTodayInStr = $dateObjToday['year'] . '-' . $dateObjToday['mon'] . '-' . $dateObjToday['mday'];


        $purchasesToday = self::where('created_at', $dateTodayInStr)->get();

        return $purchasesToday;
    }
}
