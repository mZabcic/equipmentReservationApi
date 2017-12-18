<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kit extends Model
{


    protected $table = 'kits';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];


 
}