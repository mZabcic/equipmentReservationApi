<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\User;
use App\Item;
use DB;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException as NotFound;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException as TokenExpired;
use Tymon\JWTAuth\Exceptions\TokenInvalidException as TokenInvalid;
use Tymon\JWTAuth\Exceptions\JWTException as TokenException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException as TokenBlacklisted;
use Exception;
use Hash;



class ItemsController extends Controller
{

  


/**
     * Dohvati listu svih itema
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/items",
     *     description="Dohvati sve iteme",
     *     operationId="api.users.all",
     *     produces={"application/json"},
     *     tags={"user.final"},
     *     schemes={"http"},
     *     @SWG\Response(
     *         response=200,
     *         description="Vraca listu korisnika"
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
     * )
     */
    public function getAll() {
      $items = Item::with("kit")->with("subtype")->with("type")->with("deviceType")->get();
      return response()->json($items, 200);
    }


    /**
     * Dohvati listu svih itema
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/items",
     *     description="Dohvati sve iteme",
     *     operationId="api.users.all",
     *     produces={"application/json"},
     *     tags={"user.final"},
     *     schemes={"http"},
     *     @SWG\Response(
     *         response=200,
     *         description="Vraca listu korisnika"
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
     * )
     */
    public function create(Request $request) {
        $data['identifier'] = $request->input('identifier');
        $data['description'] = $request->input('description');
      
         $data['picture'] = $request->file('picture');
           $data['kit_id'] = $request->input('kit_id');
           $data['type_id'] = $request->input('type_id');
           $data['subtype_id'] = $request->input('subtype_id');
           $data['device_type_id'] = $request->input('device_type_id');
 if ($data['identifier'] == null)
     return response()->json([
   'error' => 'Identifier is required'
], 400);
if ($data['description'] == null)
return response()->json([
'error' => 'Description is required'
], 400);

$item = Item::create([
    'identifier' => $data['identifier'],
    'description' =>  $data['description'],
    'picture' => $data['picture'],
     'kit_id' => $data['kit_id'],
     'type_id' => $data['type_id'],
     'subtype_id' =>  $data['subtype_id'],
     'device_type_id' => $data['device_type_id']
]);
      }
  

    public function guard()
    {
        return Auth::guard();
    }


}
