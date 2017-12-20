<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * @SWG\Definition(type="object")
 */
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

       /**
     * @SWG\Property(format="int")
     * @var int
     */
    private $id;
    /**
    * @SWG\Property(format="string")
    * @var int
    */
  private $label;
   /**
    * @SWG\Property(format="string")
    * @var string
    */
   private $description;
 
       /**
    * @SWG\Property(format="date")
    * @var date
    */
   private $created_at;
 
         /**
    * @SWG\Property(format="date")
    * @var date
    */
       private $updated_at;
 
 
}