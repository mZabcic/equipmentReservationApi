<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeviceType extends Model
{


    protected $table = 'device_types';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label', 'description'
    ];


 
}