<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicleocuppant extends Model
{
    use HasFactory;

    public const ACTIVO             = 0;
    public const INACTIVO            = 1; 

    protected $table = "vehicleocuppants";
    protected $fillable = [
        'id',
        'vehicle_id',
        'user_id',
        'usertype_id',
        'status'
    ];
    protected $attributes = [
         'status' => 0
    ];
}
