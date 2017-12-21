<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * @SWG\Definition(type="object")
 */
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

    public function reservations()
    {
        return $this->belongsToMany('App\Reservation', 'reservation_items', 'item_id', 'reservation_id');
    }



      /**
     * @SWG\Property(format="int")
     * @var int
     */
   private $id;
   /**
   * @SWG\Property(format="string")
   * @var string
   */
 private $identifier;
  /**
   * @SWG\Property(format="string")
   * @var string
   */
  private $description;
  /**
   * @SWG\Property(format="string")
   * @var string
   */
  private $picture;
 /**
     * @SWG\Property(format="int")
     * @var int
     */
    private $kit_id;
    /**
     * @SWG\Property(format="int")
     * @var int
     */
    private $type_id;

    /**
     * @SWG\Property(format="int")
     * @var int
     */
    private $subtype_id;

    /**
     * @SWG\Property(format="int")
     * @var int
     */
    private $device_type_id;
     /**
     * @SWG\Property(format="file")
     * @var file
     */
    private $file;
    

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

 /**
     * @SWG\Property(format="Kit")
     * @var Kit
     */
    private $kit;
    /**
     * @SWG\Property(format="Type")
     * @var Type
     */
    private $type;

    /**
     * @SWG\Property(format="Subtype")
     * @var Subtype
     */
    private $subtype;

    /**
     * @SWG\Property(format="DeviceType")
     * @var DeviceType
     */
    private $device_type;
    

 
}
