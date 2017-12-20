<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * @SWG\Definition(type="object")
 */
class DetailsResponse extends Model
{





 /**
     *@SWG\Property(@SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Kit")))
     * @var Kit[]
     */
    private $kits;
    /**
     * @SWG\Property(@SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Type")))
     * @var Type[]
     */
    private $types;

    /**
     * @SWG\Property(@SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Subtype")))
     * @var Subtype[]
     */
    private $subtypes;

    /**
     * @SWG\Property(@SWG\Schema(type="array", @SWG\Items(ref="#/definitions/DeviceType")))
     * @var DeviceType[]
     */
    private $device_types;
    

 
}
