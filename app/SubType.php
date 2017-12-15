<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubType extends Model
{


    protected $table = 'subtypes';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label', 'description'
    ];


 
}