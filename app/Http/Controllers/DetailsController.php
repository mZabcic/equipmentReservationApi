<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\User;
use App\Item;
use App\DeviceType;
use App\Kit;
use App\SubType;
use App\Type;
use App\ReservationStatus;
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



/**
     * Dohvati sve detalje itema
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="items/details",
     *     description="Dohvati sve detalje itema",
     *     operationId="api.items.details",
     *     produces={"application/json"},
     *     tags={"details"},
     *     schemes={"http"},
     *       @SWG\Parameter(
     *         name="filter",
     *         in="query",
     *         description="Pisati u formatu <ime_kolone_u_tablici>=<pojam_za_pretraživanje>",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string")
     *     ),
     *     @SWG\Response(
     *        response=200,
     *        description="Detalji itema" ,
     *  @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/DetailsResponse"))
     *     ),
     *    @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
      *    @SWG\Response(
     *         response=501,
     *         description="Invalid search data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=402,
     *         description="No token recived",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     )
*
      *   
     * )
     */
    public function getAll() {
        $deviceType = DeviceType::all();
        $kit = Kit::all();
        $subType = Subtype::all();
        $type = Type::all();
        return response()->json(['device types' => $deviceType, 'kits' => $kit, 'subtypes' => $subType, 'types' => $type], 200);
    
    }



    /**
     * Dohvati sve tipove uređaja ili filtrirane
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="items/details/devicetypes",
     *     description="Dohvati sve tipove uređaja ili filtrirane",
     *     operationId="api.items.details.devicetypes",
     *     produces={"application/json"},
     *     tags={"details"},
     *     schemes={"http"},
     *       @SWG\Parameter(
     *         name="filter",
     *         in="query",
     *         description="Pisati u formatu <ime_kolone_u_tablici>=<pojam_za_pretraživanje>",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Device types" ,
     *       
     *   @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/DeviceType"))  
     *     ),
     *    @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
      *    @SWG\Response(
     *         response=501,
     *         description="Invalid search data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=402,
     *         description="No token recived",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     )
*
      *   
     * )
     */
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

   /**
     * Dohvati sve kitove ili filtrirane
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="items/details/kits",
     *     description="Dohvati sve kitove ili filtrirane",
     *     operationId="api.items.details.kits",
     *     produces={"application/json"},
     *     tags={"details"},
     *     schemes={"http"},
     *       @SWG\Parameter(
     *         name="filter",
     *         in="query",
     *         description="Pisati u formatu <ime_kolone_u_tablici>=<pojam_za_pretraživanje>",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Kits" ,
     *       
     *   @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Kit"))  
     *     ),
     *    @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
      *    @SWG\Response(
     *         response=501,
     *         description="Invalid search data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=402,
     *         description="No token recived",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     )
*
      *   
     * )
     */
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

    /**
     * Dohvati sve podtipove uređaja ili filtrirane
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="items/details/subtypes",
     *     description="Dohvati sve podtipove uređaja ili filtrirane",
     *     operationId="api.items.details.subtypes",
     *     produces={"application/json"},
     *     tags={"details"},
     *     schemes={"http"},
     *       @SWG\Parameter(
     *         name="filter",
     *         in="query",
     *         description="Pisati u formatu <ime_kolone_u_tablici>=<pojam_za_pretraživanje>",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Subtypes" ,
     *       
     *   @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/SubType"))  
     *     ),
     *    @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
      *    @SWG\Response(
     *         response=501,
     *         description="Invalid search data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=402,
     *         description="No token recived",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     )
*
      *   
     * )
     */
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

  /**
     * Dohvati sve tipove ili filtrirane
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="items/details/types",
     *     description="Dohvati sve tipove ili filtrirane",
     *     operationId="api.items.details.types",
     *     produces={"application/json"},
     *     tags={"details"},
     *     schemes={"http"},
     *       @SWG\Parameter(
     *         name="filter",
     *         in="query",
     *         description="Pisati u formatu <ime_kolone_u_tablici>=<pojam_za_pretraživanje>",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Types" ,
     *       
     *   @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Type"))  
     *     ),
     *    @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
      *    @SWG\Response(
     *         response=501,
     *         description="Invalid search data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=402,
     *         description="No token recived",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     )
*
      *   
     * )
     */
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


    /**
     * Dohvati sve statuse rezervacija ili filtrirane
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="reservations/details",
     *     description="Dohvati sve statuse rezervacija ili filtrirane",
     *     operationId="api.reservations.details.statuses",
     *     produces={"application/json"},
     *     tags={"details"},
     *     schemes={"http"},
     *       @SWG\Parameter(
     *         name="filter",
     *         in="query",
     *         description="Pisati u formatu <ime_kolone_u_tablici>=<pojam_za_pretraživanje>",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Reservation statusi" ,
     *       
     *   @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/ReservationStatus"))  
     *     ),
     *    @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
      *    @SWG\Response(
     *         response=501,
     *         description="Invalid search data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=402,
     *         description="No token recived",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     )
*
      *   
     * )
     */
    public function getStatuses(Request $request) {
        if (count($request->query()) == 1) {
          try {
                $key = key($request->query());
            $value = $request->query($key);
            $statuses = Subtype::where($key, 'like', '%' . $value . '%')->get();
            return response()->json($statuses, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error'=>'Invalid serach data'], 501);
        }
         }
        $statuses = ReservationStatus::all();
        return response()->json($statuses, 200);
    
    }


       /**
     * Obriši tip uređaja
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Delete(
     *     path="admin/items/details/devicetypes/delete/{id}",
     *     description="Obriši tip uredđaja sa danim ID-om",
     *     operationId="api.admin.items.details.devicetypes.delete",
     *     produces={"application/json"},
     *     tags={"admin"},
     *     schemes={"http"},
     *     @SWG\Parameter(
	 * 			name="authorization",
	 * 		    in="header",
	 * 			required=true,
	 * 			type="string",
	 * 			description="JWT token",
      *         @SWG\Items(type="string")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Tip uređaja je obrisan"   
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="No data found",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *    @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
      *    @SWG\Response(
     *         response=400,
     *         description="Invalid data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=402,
     *         description="No token recived",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     ),
     *      @SWG\Response(
     *         response=403,
     *         description="No admin rights",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
*
      *   
     * )
     */
    public function deleteDeviceType($id) {
        try {
        $deviceType = DeviceType::where('id', '=', $id)->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No device type found'], 404);
    }
        $deviceType->delete();
    return response()->json();
    
    }


        /**
     * Obriši podtip
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Delete(
     *     path="admin/items/details/subtypes/delete/{id}",
     *     description="Obriši podtip sa danim ID-om",
     *     operationId="api.admin.items.details.subtypes.delete",
     *     produces={"application/json"},
     *     tags={"admin"},
     *     schemes={"http"},
     *     @SWG\Parameter(
	 * 			name="authorization",
	 * 		    in="header",
	 * 			required=true,
	 * 			type="string",
	 * 			description="JWT token",
      *         @SWG\Items(type="string")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Podtip je obrisan"   
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="No data found",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *    @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
      *    @SWG\Response(
     *         response=400,
     *         description="Invalid data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=402,
     *         description="No token recived",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     ),
     *      @SWG\Response(
     *         response=403,
     *         description="No admin rights",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
*
      *   
     * )
     */
    public function deleteSubtype($id) {
        try {
        $Subtype = SubType::where('id', '=', $id)->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No subtype found'], 404);
    }
    $Subtype->delete();
    return response()->json();
    
    }

        /**
     * Obriši tip
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Delete(
     *     path="admin/items/details/types/delete/{id}",
     *     description="Obriši tip sa danim ID-om",
     *     operationId="api.admin.items.details.types.delete",
     *     produces={"application/json"},
     *     tags={"admin"},
     *     schemes={"http"},
     *     @SWG\Parameter(
	 * 			name="authorization",
	 * 		    in="header",
	 * 			required=true,
	 * 			type="string",
	 * 			description="JWT token",
      *         @SWG\Items(type="string")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Tip je obrisan"   
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="No data found",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *    @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
      *    @SWG\Response(
     *         response=400,
     *         description="Invalid data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=402,
     *         description="No token recived",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     ),
     *      @SWG\Response(
     *         response=403,
     *         description="No admin rights",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
*
      *   
     * )
     */
    public function deleteType($id) {
        try {
        $Type = Type::where('id', '=', $id)->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No type found'], 404);
    }
    $Type->delete();
    return response()->json();
    
    }


            /**
     * Obriši kit
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Delete(
     *     path="admin/items/details/kits/delete/{id}",
     *     description="Obriši kit sa danim ID-om",
     *     operationId="api.admin.items.details.kits.delete",
     *     produces={"application/json"},
     *     tags={"admin"},
     *     schemes={"http"},
     *     @SWG\Parameter(
	 * 			name="authorization",
	 * 		    in="header",
	 * 			required=true,
	 * 			type="string",
	 * 			description="JWT token",
      *         @SWG\Items(type="string")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Kit je obrisan"   
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="No data found",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *    @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
      *    @SWG\Response(
     *         response=400,
     *         description="Invalid data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=402,
     *         description="No token recived",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     ),
     *      @SWG\Response(
     *         response=403,
     *         description="No admin rights",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
*
      *   
     * )
     */
    public function deleteKit($id) {
        try {
        $kit = Kit::where('id', '=', $id)->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No kit found'], 404);
    }
    $kit->delete();
    return response()->json();
    
    }



         /**
     * Kreiranje device tipa
     * @param string $label
     * @param string $description
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="admin/items/details/create/devicetype",
     *     description="Kreiranje device tipa",
     *     operationId="api.admin.items.details.create.devicetype",
     *     produces={"application/json"},
     *     tags={"admin"},
     *     schemes={"http"},
     *      @SWG\Parameter(
	 * 			name="authorization",
	 * 		    in="header",
	 * 			required=true,
	 * 			type="string",
	 * 			description="JWT token",
      *         @SWG\Items(type="string")
	 * 		),
     *     @SWG\Parameter(
	 * 			name="label",
	 * 		    in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Labela",
     * @SWG\Schema(type="string")
	 * 		),
     *    @SWG\Parameter(
	 * 			name="description",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Opis",
     * @SWG\Schema(type="string")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Device type created"
     *     ),
     *     @SWG\Response(
     *         response=409,
     *         description="Item with that label already exists",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid form data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=402,
     *         description="No token recived",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     ),
     *       @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=403,
     *         description="No admin rights",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
     * )
     */
    public function createDeviceType(Request $request) {
        $data['label'] = $request->input('label');
        $data['description'] = $request->input('description');
      
 if ($data['label'] == null)
     return response()->json([
   'error' => 'Label is required'
], 400);
if ($data['description'] == null)
return response()->json([
'error' => 'Description is required'
], 400);
$check = DeviceType::where('label', $data['label'] )->count();
if ($check > 0) {
  return response()->json([
'error' => 'Device type with that identifier already exists'
], 409);
}
$item = DeviceType::create([
    'label' => $data['label'],
    'description' =>  $data['description']
]);
      }

   
         /**
     * Kreiranje  tipa
     * @param string $label
     * @param string $description
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="admin/items/details/create/type",
     *     description="Kreiranje tipa",
     *     operationId="api.admin.items.details.create.type",
     *     produces={"application/json"},
     *     tags={"admin"},
     *     schemes={"http"},
     *      @SWG\Parameter(
	 * 			name="authorization",
	 * 		    in="header",
	 * 			required=true,
	 * 			type="string",
	 * 			description="JWT token",
      *         @SWG\Items(type="string")
	 * 		),
     *     @SWG\Parameter(
	 * 			name="label",
	 * 		    in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Labela",
     * @SWG\Schema(type="string")
	 * 		),
     *    @SWG\Parameter(
	 * 			name="description",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Opis",
     * @SWG\Schema(type="string")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Type created"
     *     ),
     *     @SWG\Response(
     *         response=409,
     *         description="Item with that label already exists",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid form data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=402,
     *         description="No token recived",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     ),
     *       @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=403,
     *         description="No admin rights",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
     * )
     */
    public function createType(Request $request) {
        $data['label'] = $request->input('label');
        $data['description'] = $request->input('description');
      
 if ($data['label'] == null)
     return response()->json([
   'error' => 'Label is required'
], 400);
if ($data['description'] == null)
return response()->json([
'error' => 'Description is required'
], 400);
$check = Type::where('label', $data['label'] )->count();
if ($check > 0) {
  return response()->json([
'error' => 'Type with that identifier already exists'
], 409);
}
$item = Type::create([
    'label' => $data['label'],
    'description' =>  $data['description']
]);
      }

               /**
     * Kreiranje  podtipa
     * @param string $label
     * @param string $description
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="admin/items/details/create/subtype",
     *     description="Kreiranje podtipa",
     *     operationId="api.admin.items.details.create.subtype",
     *     produces={"application/json"},
     *     tags={"admin"},
     *     schemes={"http"},
     *      @SWG\Parameter(
	 * 			name="authorization",
	 * 		    in="header",
	 * 			required=true,
	 * 			type="string",
	 * 			description="JWT token",
      *         @SWG\Items(type="string")
	 * 		),
     *     @SWG\Parameter(
	 * 			name="label",
	 * 		    in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Labela",
     * @SWG\Schema(type="string")
	 * 		),
     *    @SWG\Parameter(
	 * 			name="description",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Opis",
     * @SWG\Schema(type="string")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Subtype created"
     *     ),
     *     @SWG\Response(
     *         response=409,
     *         description="Item with that label already exists",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid form data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=402,
     *         description="No token recived",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     ),
     *       @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=403,
     *         description="No admin rights",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
     * )
     */
    public function createSubType(Request $request) {
        $data['label'] = $request->input('label');
        $data['description'] = $request->input('description');
      
 if ($data['label'] == null)
     return response()->json([
   'error' => 'Label is required'
], 400);
if ($data['description'] == null)
return response()->json([
'error' => 'Description is required'
], 400);
$check = SubType::where('label', $data['label'] )->count();
if ($check > 0) {
  return response()->json([
'error' => 'Subtype with that identifier already exists'
], 409);
}
$item = SubType::create([
    'label' => $data['label'],
    'description' =>  $data['description']
]);
      }


                     /**
     * Kreiranje  kita
     * @param string $label
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="admin/items/details/create/kit",
     *     description="Kreiranje kita",
     *     operationId="api.admin.items.details.create.kit",
     *     produces={"application/json"},
     *     tags={"admin"},
     *     schemes={"http"},
     *      @SWG\Parameter(
	 * 			name="authorization",
	 * 		    in="header",
	 * 			required=true,
	 * 			type="string",
	 * 			description="JWT token",
      *         @SWG\Items(type="string")
	 * 		),
     *     @SWG\Parameter(
	 * 			name="name",
	 * 		    in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Labela",
     * @SWG\Schema(type="string")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Kit created"
     *     ),
     *     @SWG\Response(
     *         response=409,
     *         description="Item with that label already exists",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid form data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=402,
     *         description="No token recived",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     ),
     *       @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=403,
     *         description="No admin rights",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
     * )
     */
    public function createKit(Request $request) {
        $data['name'] = $request->input('label');
      
 if ($data['name'] == null)
     return response()->json([
   'error' => 'Name is required'
], 400);

$check = Kit::where('name', $data['name'] )->count();
if ($check > 0) {
  return response()->json([
'error' => 'Kit with that identifier already exists'
], 409);
}
$item = SubType::create([
    'kit' => $data['kit']
]);
      }






     

    

    public function guard()
    {
        return Auth::guard();
    }


   


}
