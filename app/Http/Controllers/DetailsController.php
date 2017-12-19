<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\User;
use App\Item;
use App\DeviceType;
use App\Kit;
use App\SubType;
use App\Type;
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



class DetailsController extends Controller
{




    public function getAll() {
        $deviceType = DeviceType::all();
        $kit = Kit::all();
        $subType = Subtype::all();
        $type = Type::all();
        return response()->json(['device types' => $deviceType, 'kits' => $kit, 'subtypes' => $subType, 'types' => $type], 200);
    
    }


    public function getDeviceTypes(Request $request) {
        if (count($request->query()) == 1) {
         try {   $key = key($request->query());
            $value = $request->query($key);
            $deviceType = DeviceType::where($key, 'like', '%' . $value . '%')->get();
            return response()->json($deviceType, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error'=>'Invalid serach data'], 501);
        }
         }
        $deviceType = DeviceType::all();
        return response()->json( $deviceType, 200);
    
    }

 
    public function getKits(Request $request) {

        if (count($request->query()) == 1) {
          try {
                $key = key($request->query());
            $value = $request->query($key);
            $kits = Kit::where($key, 'like', '%' . $value . '%')->get();
            return response()->json($kits, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error'=>'Invalid serach data'], 501);
        }
         }
      
        $kit = Kit::all();
       
        return response()->json( $kit, 200);
    
    }

  
    public function getSubtypes(Request $request) {
        if (count($request->query()) == 1) {
          try {
                $key = key($request->query());
            $value = $request->query($key);
            $subtype = Subtype::where($key, 'like', '%' . $value . '%')->get();
            return response()->json($subtype, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error'=>'Invalid serach data'], 501);
        }
         }
        $subType = Subtype::all();
        return response()->json($subType, 200);
    
    }


    public function getTypes(Request $request) {
        if (count($request->query()) == 1) {
        try {
           $key = key($request->query());
           $value = $request->query($key);
           $type = Type::where($key, 'like', '%' . $value . '%')->get();
           return response()->json($type, 200);
        } catch (Illuminate\Database\QueryException $e) {
            return response()->json(['error'=>'Invalid serach data'], 501);
        }
        }
        $type = Type::all();
        return response()->json($type, 200);
    
    }

    

    public function guard()
    {
        return Auth::guard();
    }


   


}
