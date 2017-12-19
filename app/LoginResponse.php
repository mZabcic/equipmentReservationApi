<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * @SWG\Definition(type="object")
 */
class LoginResponse extends Model 
{
	/**
     * @SWG\Property(format="string")
     * @var string
     */
	private $token;
	/**
     * @SWG\Property(format="User")
     * @var User
     */
	private $user;
}
