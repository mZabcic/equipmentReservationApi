<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{


    protected $table = 'types';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label', 'description'
    ];


 
}