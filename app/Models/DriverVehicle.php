<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverVehicle extends Model
{
    use HasFactory;

    public $table = 'driver_vehicle';

    public $fillable = [
        'driver_id',
        'vehicle_id',
        'company_id',
    ];

}
