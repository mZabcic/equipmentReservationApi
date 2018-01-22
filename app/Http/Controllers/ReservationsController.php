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
use App\Reservation;
use App\ReservationItem;
use App\Extend;
use DB;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException as NotFound;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Hash;
use DateTime;
use Carbon\Carbon;


class ReservationsController extends Controller
{

    /**
     * Zatraži opremu
     * @param string $start_date
     * @param string $return_date
     * @param string[] $item_id
     *  @param string[] $remark
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="reservations/request",
     *     description="Zatraži opremu",
     *     operationId="api.reservations.request",
     *     produces={"application/json"},
     *     tags={"reservations"},
     *     schemes={"http"},
     *     @SWG\Parameter(
	 * 			name="start_date",
	 * 		    in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Datum početka posudbe",
     * @SWG\Schema(type="string")
	 * 		),
     *    @SWG\Parameter(
	 * 			name="return_date",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Datum vraćanja opreme, null ukoliko ne znaš kolko ti treba",
     * @SWG\Schema(type="string")
	 * 		),
     *    @SWG\Parameter(
	 * 			name="item_id",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Ime",
   *   @SWG\Schema(type="array", @SWG\Items(type="string"))  
	 * 		),
     *    @SWG\Parameter(
	 * 			name="remark",
	 * 			in="body",
	 * 			required=false,
	 * 			type="string",
	 * 			description="Napomena uz posudbu",
     *           @SWG\Schema(type="string")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Request made"
     *     ),
     *     @SWG\Response(
     *         response=409,
     *         description="User exists",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid form data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=411,
     *         description="Daje listu rezervacija s itemima koji su zauzeti u tom periodu",
     *         @SWG\Schema(ref="#/definitions/ReservationError")
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
     * )
     */
    public function reservationRequest(Request $request){
        $me = $this->guard()->user();
        if ($request->input('start_date') == null)
        return response()->json([
      'error' => 'Start date is required'
  ], 400);
  if ($request->input('return_date') == null)
  return response()->json([
'error' => 'Return date is required'
], 400);
        $data['start_date'] = DateTime::createFromFormat('d.m.Y', $request->input('start_date'));
        $data['start_date'] =  $data['start_date']->format('Y-m-d');
          $data['return_date'] = DateTime::createFromFormat('d.m.Y', $request->input('return_date'));
          $data['return_date'] =  $data['return_date']->format('Y-m-d');
        if ($data['return_date'] <  $data['start_date']) {
            return response()->json([
                'error' => 'Start date is bigger than return date'
                ], 400);
        }
           $data['item_id'] = json_decode($request->input('item_id'));
             $data['remark'] = $request->input('remark');
       
   if ($data['item_id'] == null)
       return response()->json([
     'error' => 'Items are required'
 ], 400);
$check = $this->checkIfItemsTaken($data['start_date'], $data['return_date'], $data['item_id']);
if (count($check) != 0) {
    return response()->json([
        'reservations' => $check,
        'error' => 'Items are reserved for this period, change items or period'
    ], 411);
}

 $reservation = new Reservation;
 $reservation->user_id = $me->id;
 $reservation->start_date = $data['start_date'];
 $reservation->return_date = $data['return_date'];
 $reservation->remark = $data['remark'];
 $reservation->status_id = 1;
 $reservation->status_by_id = $me->id;
 $reservation->save();

 foreach($data['item_id'] as $item) {
    $reservationItem = DB::table('reservation_items')->insert([
        'reservation_id' => $reservation->id,
        'item_id' => $item,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
  ]);
 }

}



/**
     * Dohvati sve rezervacije
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="reservations",
     *     description="Dohvati sve items ili filtrirane",
     *     operationId="api.reservations",
     *     produces={"application/json"},
     *     tags={"reservations"},
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
     *         description="Items" ,
     *       
     *   @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Item"))  
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
    public function all(Request $request) {
        if (count($request->query()) == 1) {
            try {
               $key = key($request->query());
               $value = $request->query($key);
               $item = Reservation::with('items.item')->with('user')->with('status')->with('extends')->with('status_creator')->where('status_id', '!=', 5)->where($key, '=', $value )->get();
               return response()->json($item, 200);
            } catch (Illuminate\Database\QueryException $e) {
                return response()->json(['error'=>'Invalid serach data'], 501);
            }
      }
      $reservations = Reservation::with('items.item')->with('user')->with('status')->with('extends')->with('status_creator')->where('status_id', '!=', 5)->get();
      return response()->json($reservations, 200);
    }


    


/**
     * Dohvati sve zahtjeve za produživanjem
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="admin/reservations/extends",
     *     description="Dohvati sve rezervacije sa zahtjevom za produživanje",
     *     operationId="api.reservations.extend.all",
     *     produces={"application/json"},
     *     tags={"reservations"},
     *     schemes={"http"},
     *     @SWG\Response(
     *         response=200,
     *         description="Reservations with extend request" ,
     *       
     *   @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Extend"))  
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
    public function allExtends() {
       
      $reservations = Extend::with('reservation.items.item')->with('reservation.status')->get();
      return response()->json($reservations, 200);
    }


    /**
     * Dohvati sve zahtjeve za produživanjem koje sam ja kreirao
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/reservations/extends",
     *     description="Dohvati sve moje rezervacije sa zahtjevom za produživanje",
     *     operationId="api.reservations.extend.all",
     *     produces={"application/json"},
     *     tags={"reservations"},
     *     schemes={"http"},
     *     @SWG\Response(
     *         response=200,
     *         description="Reservations with extend request" ,
     *       
     *   @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Extend"))  
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
    public function myExtends() {
        $me = $this->guard()->user();
       $reservations = Extend::with('reservation.items.item')->with('reservation.status')->where('user_id', $me->id)->get();
       return response()->json($reservations, 200);
     }
 
 



       /**
     * Stavlja rezervaciju u status otkazan
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Delete(
     *     path="reservations/delete/{id}",
     *     description="Otkazuje rezervaciju sa danim ID-om",
     *     operationId="api.reservations.delete",
     *     produces={"application/json"},
     *     tags={"reservations"},
     *     schemes={"http"},
     *     @SWG\Parameter(
	 * 			name="authorization",
	 * 		    in="header",
	 * 			required=true,
	 * 			type="string",
	 * 			description="JWT token",
      *         @SWG\Items(type="string")
	 * 		),
     *   *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id rezervacije",
     *         required=true,
     *         type="integer",
     *         @SWG\Items(type="integer")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Rezervacija je otkazana"   
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
     *         description="Not your reservation",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     )
*
      *   
     * )
     */
    public function delete($id) {
        $me = $this->guard()->user();
        try {
        $resevation = Reservation::where('id', $id)->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No reservation found'], 404);
    }
        if ($me->role_id == 1 || $resevation->user_id == $me->id) {
           $resevation->status_id = 5;
           $reservation->status_by_id = $me->id;
           $resevation->save();
           return response()->json();
        } else {
            return response()->json(['error'=>'Not your reservation'], 403);
        }
    
    }


        /**
     * Stavlja rezervaciju u status odobrena
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="admin/reservations/approve",
     *     description="Odobrava rezervaciju sa danim ID-om",
     *     operationId="api.admin.approve",
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
     *   *     @SWG\Parameter(
     *         name="id",
     *         in="body",
     *         description="Id rezervacije",
     *         required=true,
     *         type="string",
     *       * @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Rezervacija je odobrena"   
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
    public function approve(Request $request) {
        $me = $this->guard()->user();
        if ($request->input('id') == null)
        return response()->json([
      'error' => 'ID is required'
  ], 400);
        try {
        $resevation = Reservation::where('id', '=', $request->input('id'))->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No reservation found'], 404);
    }
           $resevation->status_id = 2;
        
     
           $resevation->status_by_id = $me->id;
           $resevation->save();
           return response()->json();
       
    
    }


           /**
     * Stavlja rezervaciju u status vraćeno
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="admin/reservations/return",
     *     description="Sve stavke vraćene za rezervaciju sa danim ID-om",
     *     operationId="api.admin.returned",
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
     *   *     @SWG\Parameter(
     *         name="id",
     *         in="body",
     *         description="Id rezervacije",
     *         required=true,
     *         type="string",
     *       * @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Stavke su vraćene"   
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
    public function returned(Request $request) {
        if ($request->input('id') == null)
        return response()->json([
      'error' => 'ID is required'
  ], 400);
        try {
        $resevation = Reservation::where('id', '=', $request->input('id'))->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No reservation found'], 404);
    }
           $resevation->status_id = 4;
           $me = $this->guard()->user();
           $reservation->status_by_id = $me->id;
           $resevation->save();
           return response()->json();
       
    
    }


    
           /**
     * Obijanje rezervacije
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="admin/reservations/decline",
     *     description="Odbijena je rezervacija sa danim ID-om",
     *     operationId="api.admin.decline",
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
     *   *     @SWG\Parameter(
     *         name="id",
     *         in="body",
     *         description="Id rezervacije",
     *         required=true,
     *         type="string",
     *       * @SWG\Schema(type="string")
     *     ),
     *    *   *     @SWG\Parameter(
     *         name="remark",
     *         in="body",
     *         description="Razlog odbijanja",
     *         required=true,
     *         type="string",
     *       * @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Rezervacija odbijena"   
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
    public function declined(Request $request) {
        if ($request->input('id') == null)
        return response()->json([
      'error' => 'ID is required'
  ], 400);
  if ($request->input('remark') == null)
  return response()->json([
'error' => 'Remark is required'
], 400);
        try {
        $resevation = Reservation::where('id', '=', $request->input('id'))->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No reservation found'], 404);
    }
           $resevation->status_id = 3;
           $resevation->remark = $request->input('remark');
           $me = $this->guard()->user();
           $reservation->status_by_id = $me->id;
           $resevation->save();
           return response()->json();
       
    
    }


    
  
    






     
private function checkIfItemsTaken($start_date, $end_date, $items) {
      $returnData = [];
      foreach ($items as $item) {
         $data = Item::with('reservations')->where('id', $item)->firstOrFail();
      
        $data->reservations = $data->reservations->filter(function ($value, $key) use ($start_date, $end_date) {
      
            return $value->status_id == 2;
        });
           
         $test = $data->reservations->filter(function ($value, $key) use ($start_date, $end_date) {
            if ($value->returned_date == null) {
                $value->returned_date = '9999-12-31';
            }
           return DateTime::createFromFormat('Y-m-d', $value->start_date)  <= DateTime::createFromFormat('Y-m-d',$start_date) && DateTime::createFromFormat('Y-m-d', $value->returned_date)  >= DateTime::createFromFormat('Y-m-d', $end_date);
        });
        if (count($test) > 0) {
        $res = Reservation::with('items')->where('id', $test[0]->id)->firstOrFail();
        array_push($returnData, $res);
        }
      }
      return $returnData;
    
}
    


/**
     * Dohvati sve rezervacije nekog korisnika
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="reservations/user/{id}",
     *     description="Dohvati sve items ili filtrirane od nekog korisnika",
     *     operationId="api.reservations.users",
     *     produces={"application/json"},
     *     tags={"reservations"},
     *     schemes={"http"},
     *        @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id korisnika",
     *         required=true,
     *         type="integer",
     *         @SWG\Items(type="integer")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Rezervacije korisnika" ,
     *       
     *   @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Reservation"))  
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
    public function byUser($id) {
      $reservations = Reservation::with('items.item')->with('user')->with('status')->where('user_id', $id)->with('status_creator')->with('extends')->get();
      return response()->json($reservations, 200);
    }


    /**
     * Dohvati sve rezervacije nekog itema
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="reservations/item/{id}",
     *     description="Dohvati sve items ili filtrirane od nekog itema",
     *     operationId="api.reservations.items",
     *     produces={"application/json"},
     *     tags={"reservations"},
     *     schemes={"http"},
     *        @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id itema",
     *         required=true,
     *         type="integer",
     *         @SWG\Items(type="integer")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Rezervacije po itemu" ,
     *       
     *   @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Reservation"))  
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
    public function byItem($id) {
        $reservations = Item::with('reservations.user')->with('reservations.status')->with('reservations.extends')->with('status_creator')->where('id', $id)->get();
        return response()->json($reservations, 200);
      }





          /**
     * Zatraži produženje
     * @param string $start_date
     * @param string $return_date
     * @param string[] $item_id
     *  @param string[] $remark
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="reservations/extend",
     *     description="Zatraži produženje rezervacije opreme",
     *     operationId="api.reservations.request.extend",
     *     produces={"application/json"},
     *     tags={"reservations"},
     *     schemes={"http"},
     *    @SWG\Parameter(
	 * 			name="new_return_date",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Novi datum vraćanja opreme",
     * @SWG\Schema(type="string")
	 * 		),
     *    @SWG\Parameter(
	 * 			name="reservation_id",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Ime",
   *   @SWG\Schema(type="array", @SWG\Items(type="string"))  
	 * 		),
     *    @SWG\Parameter(
	 * 			name="reason",
	 * 			in="body",
	 * 			required=false,
	 * 			type="string",
	 * 			description="Napomena uz produženje",
     *           @SWG\Schema(type="string")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Zahtjev za produžetkom zatražen"
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
     *      @SWG\Response(
     *         response=404,
     *         description="No reservation found",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     )
     * ,
     *      @SWG\Response(
     *         response=405,
     *         description="Not your reservation",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     )   ,
     *      @SWG\Response(
     *         response=406,
     *         description="Wrong reservation status",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     )
     * )
     */
    public function extendRequest(Request $request){
        $me = $this->guard()->user();
        if ($request->input('new_return_date') == null)
        return response()->json([
      'error' => 'New return date is required'
  ], 400);
  if ($request->input('reservation_id') == null)
  return response()->json([
'error' => 'Reservation id is required'
], 400);
$data['reservation_id'] = $request->input('reservation_id');
$data['reason'] = $request->input('reason');
       try {
           $reservation = Reservation::where('id',$data['reservation_id'])->with('items')->firstOrfail();
       } catch (NotFound $e) {
        return response()->json(['error' => 'No reservation found'], 406);
    }
    if ($reservation->status_id != 2) {
        return response()->json(['error' => 'Reservation must be in status_id 2'], 406);
    }
if ($reservation->user_id != $me->id) {
    return response()->json(['error' => 'Not your reservation'], 405);
}
        $data['new_return_date'] = DateTime::createFromFormat('d.m.Y', $request->input('new_return_date'));
        $data['new_return_date'] =  $data['new_return_date']->format('Y-m-d');
        $data['start_date'] =  $reservation->start_date;
        if ($data['new_return_date'] <  $data['start_date']) {
            return response()->json([
                'error' => 'Start date is bigger than return date'
                ], 400);
        }
           
        if ($data['new_return_date'] <  $reservation->return_date) {
            return response()->json([
                'error' => 'Return date is bigger than new return date'
                ], 400);
        }
  $items = array();        
 foreach ($reservation->items as $i) {
    array_push($items, $i->id);
 }    

$check = $this->checkIfItemsTaken($data['start_date'], $data['new_return_date'], $items);
if (count($check) != 0) {
    return response()->json([
        'reservations' => $check,
        'error' => 'Items are reserved for this period, change items or period'
    ], 411);
}
 $extend = new Extend;
 $extend->user_id = $me->id;
 $extend->reservation_id = $reservation->id;
 $extend->new_date_to = $data['new_return_date'];
 $extend->reason = $data['reason'];
 $extend->status = "Zatraženo produživanje";
 $extend->save();

 

}


          /**
     * Prihvati produživanje
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="admin/reservations/extend",
     *     description="Prihvaća produživanje",
     *     operationId="api.admin.extend",
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
     *   *     @SWG\Parameter(
     *         name="id",
     *         in="body",
     *         description="Id extenda",
     *         required=true,
     *         type="string",
     *       * @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Rezervacija produžena"   
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
    public function extend(Request $request) {
        if ($request->input('id') == null)
        return response()->json([
      'error' => 'ID is required'
  ], 400);
        try {
        $extend = Extend::where('id', '=', $request->input('id'))->with('reservation')->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No extension request found'], 404);
    }
    try {
        $reservation = Reservation::where('id', '=', $extend->reservation_id)->with('items')->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No reservation found'], 404);
    }
    $items = array();        
    foreach ($reservation->items as $i) {
       array_push($items, $i->id);
    }    
   
$check = $this->checkIfItemsTaken($reservation->start_date, $extend->new_date_to, $items);
if (count($check) != 0) {
    return response()->json([
        'reservations' => $check,
        'error' => 'Items are reserved for this period, change items or period'
    ], 411);
}

$reservation->return_date = $extend->new_date_to;
$extend->status = "Produženo";
$reservation->save();
$extend->save();
    
    }


    
          /**
     * Odbij produživanje
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="admin/reservations/extend/refuse",
     *     description="Odbij produživanje",
     *     operationId="api.admin.extend.block",
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
     *   *     @SWG\Parameter(
     *         name="id",
     *         in="body",
     *         description="Id extenda",
     *         required=true,
     *         type="string",
     *       * @SWG\Schema(type="string")
     *     ),
     *   *     @SWG\Parameter(
     *         name="reason",
     *         in="body",
     *         description="Razlog odbijanja",
     *         required=false,
     *         type="string",
     *       * @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Rezervacija produžena"   
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
    public function blockExtend(Request $request) {
        if ($request->input('id') == null)
        return response()->json([
      'error' => 'ID is required'
  ], 400);
        try {
        $extend = Extend::where('id', '=', $request->input('id'))->with('reservation')->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No extension request found'], 404);
    }
   $extend->status = "Odbijeno";
   $extend->reason = $request->input('reason');
   $extend->save();
    
    }


     /**
     * Obriši rezervaciju
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Delete(
     *     path="admin/reservations/delete/{id}",
     *     description="Obriši rezervaciju sa danim ID-om",
     *     operationId="api.admin.reservations.delete",
     *     produces={"application/json"},
     *     tags={"admin"},
     *     schemes={"http"},
     * *     @SWG\Parameter(
	 * 			name="authorization",
	 * 		    in="header",
	 * 			required=true,
	 * 			type="string",
	 * 			description="JWT token",
      *         @SWG\Items(type="string")
	 * 		),
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id rezervacije",
     *         required=true,
     *         type="integer",
     *         @SWG\Items(type="integer")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Rezervacija je obrisana"   
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
     * ,
     *      @SWG\Response(
     *         response=407,
     *         description="Item has reservations, you cannot delete it",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
*
      *   
     * )
     */
    public function deleteAdmin($id) {
        try {
        $reservation = Reservation::where('id', '=', $id)->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No item found'], 404);
    }
    $resItem = ReservationItem::where('reservation_id', $reservation->id)->get();
    foreach ($resItem as $item) {
        $item->delete();
    }
    $extends = Extend::where('reservation_id', $reservation->id)->get();
    foreach ($extends as $item) {
        $item->delete();
    }
    $reservation->delete();
    return response()->json();
    
    }

    public function deleteExtend($id) {
        try {
        $extend = Extend::where('id', '=', $id)->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No item found'], 404);
    }
    
    $extend->delete();
    return response()->json();
    
    }





    public function guard()
    {
        return Auth::guard();
    }


   


}
