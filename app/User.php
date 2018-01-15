<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @SWG\Definition(type="object")
 */
class User extends Authenticatable implements JWTSubject
{
    use Notifiable;


    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'role_id', 'active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
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
 private $first_name;
  /**
   * @SWG\Property(format="string")
   * @var string
   */
  private $last_name;
  /**
   * @SWG\Property(format="string")
   * @var string
   */
  private $email;
 /**
     * @SWG\Property(format="int")
     * @var int
     */
    private $role_id;

     /**
     * @SWG\Property(format="boolean")
     * @var boolean
     */
    private $active;
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
