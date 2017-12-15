<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{


    protected $table = 'items';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'identifier', 'description', 'picture', 'kit_id', 'type_id', 'subtype_id', 'device_type_id'
    ];

    public function kit()
    {
        return $this->belongsTo('App\Kit','kit_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo('App\Type','type_id', 'id');
    }

    public function subtype()
    {
        return $this->belongsTo('App\SubType','subtype_id', 'id');
    }

    public function deviceType()
    {
        return $this->belongsTo('App\DeviceType','device_type_id', 'id');
    }





 
}
