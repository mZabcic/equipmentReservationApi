<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * @SWG\Definition(type="object")
 */
class ReservationItem extends Model
{


    protected $table = 'reservation_items';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reservation_id', 'item_id'
    ];

    public function item()
    {
        return $this->belongsTo('App\Item','item_id', 'id');
    }

    public function reservation()
    {
        return $this->belongsTo('App\Reservation','reservation_id', 'id');
    }

    


      /**
     * @SWG\Property(format="int")
     * @var int
     */
   private $id;
   /**
   * @SWG\Property(format="int")
   * @var int
   */
 private $reservation_id;
    /**
     * @SWG\Property(format="int")
     * @var int
     */
    private $item_id;

  
    

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
   * @SWG\Property(format="Reservation")
   * @var Reservation
   */
  private $reservatin;
   /**
   * @SWG\Property(format="Item")
   * @var Item
   */
  private $item;

 
}