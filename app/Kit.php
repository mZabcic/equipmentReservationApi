<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * @SWG\Definition(type="object")
 */
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

           /**
     * @SWG\Property(format="int")
     * @var int
     */
   private $id;
   /**
   * @SWG\Property(format="string")
   * @var int
   */
 private $name;


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