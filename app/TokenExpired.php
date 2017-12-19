<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * @SWG\Definition(type="object")
 */
class TokenExpired extends Model
{
        /**
     * @SWG\Property(format="string")
     * @var string
     */
   private $token;
     /**
     * @SWG\Property(format="string")
     * @var string
     */
   private $error;
}
