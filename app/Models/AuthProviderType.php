<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthProviderType extends Model
{
    use HasFactory;

    public const BMD = 1;
    public const GOOGLE = 2;
    public const FACEBOOK = 3;
}
