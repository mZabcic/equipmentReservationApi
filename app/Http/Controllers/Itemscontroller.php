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

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
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
    public function getAll() {
        
 
    $items = Item::all();
    return response()->json($items, 200);
    
    }

    public function guard()
    {
        return Auth::guard();
    }

   


}
