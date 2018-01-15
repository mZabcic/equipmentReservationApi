<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * @SWG\Definition(type="object")
 */
class Extend extends Model
{


    protected $table = 'extend';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'reservation_id', 'status', 'new_date_to', 'reason'
    ];

    public function user()
    {
        return $this->belongsTo('App\User','user_id', 'id');
    }

    

    public function reservation()
    {
        return $this->belongsTo('App\Reservation',   'reservation_id', 'id');
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
 private $user_id;
  /**
   * @SWG\Property(format="int")
   * @var int
   */
  private $reservation_id;
  /**
   * @SWG\Property(format="string")
   * @var string
   */
  private $status;
 /**
     * @SWG\Property(format="date")
     * @var date
     */
    private $new_date_to;
    /**
     * @SWG\Property(format="string")
     * @var string
     */
    private $reason;


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
     * @SWG\Property(format="Reservation")
     * @var Reservation
     */
    private $reservation;

    

 
}
