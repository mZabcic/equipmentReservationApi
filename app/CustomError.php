<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * @SWG\Definition(type="object")
 */
class CustomError extends Model
{
     /**
     * @SWG\Property(format="string")
     * @var string
     */
   private $error;
}
