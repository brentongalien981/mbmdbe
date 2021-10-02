<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    use HasFactory;



    public static function getAvailableDispatches() {

        $statuses = DispatchStatus::whereIn('name', ['EP_BATCH_CREATED', 'EP_BATCH_UPDATED'])->get()->pluck('code');

        return self::whereIn('status_code', $statuses)->orderBy('created_at', 'desc')->get();
    }



    public function orders() 
    {
        return $this->hasMany(Order::class);
    }
}
