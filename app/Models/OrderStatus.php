<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;


    public static function getCodeByName($name) {
        return self::where('name', $name)->get()[0]->code;
    }
}
