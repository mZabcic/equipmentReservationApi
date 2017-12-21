<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * @SWG\Definition(type="object")
 */
class ReservationError extends Model
{
        /**
     * @SWG\Property(format="Reservation")
     * @var Reservation[]
     */
   private $reservations;
     /**
     * @SWG\Property(format="string")
     * @var string
     */
   private $error;
}
