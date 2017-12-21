<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * @SWG\Definition(type="object")
 */
class Reservation extends Model
{


    protected $table = 'reservations';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'return_date', 'returned_date', 'remark', 'status_id', 'start_date'
    ];

    public function user()
    {
        return $this->belongsTo('App\User','user_id', 'id');
    }

    public function items()
    {
      return $this->hasMany('App\ReservationItem', 'reservation_id');
    }

    public function status()
    {
        return $this->belongsTo('App\ReservationStatus','status_id', 'id');
    }

    


      /**
     * @SWG\Property(format="int")
     * @var int
     */
   private $id;
   /**
   * @SWG\Property(format="string")
   * @var int
   */
 private $user_id;
  /**
   * @SWG\Property(format="date")
   * @var date
   */
  private $start_date;
  /**
   * @SWG\Property(format="date")
   * @var date
   */
  private $return_date;
  /**
   * @SWG\Property(format="date")
   * @var date
   */
  private $returned_date;
 /**
     * @SWG\Property(format="string")
     * @var string
     */
    private $remark;
    /**
     * @SWG\Property(format="int")
     * @var int
     */
    private $status_id;

  
    

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
   * @SWG\Property(format="User")
   * @var User
   */
  private $user;
    /**
   * @SWG\Property(format="ReservationStatus")
   * @var ReservationStatus
   */
  private $status;

  /**
   * @SWG\Property(@SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Item"))  )
   * @var Item[]
   */
  private $items;
   

 
}