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
use App\ReservationItems;
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
               $item = Reservation::with('items.item')->where($key, '=', $value )->get();
               return response()->json($item, 200);
            } catch (Illuminate\Database\QueryException $e) {
                return response()->json(['error'=>'Invalid serach data'], 501);
            }
      }
      $reservations = Reservation::with('items.item')->with('user')->with('status')->where('status_id', '!=', 5)->get();
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
        $resevation = Reservation::where('id', '=', $id)->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No reservation found'], 404);
    }
        if ($me->role_id == 1 || $reservation->user_id == $me->id) {
           $resevation->status_id = 5;
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
           $resevation->save();
           return response()->json();
       
    
    }


    
  
    






     
private function checkIfItemsTaken($start_date, $end_date, $items) {
      $returnData = [];
      foreach ($items as $item) {
         $data = Item::with('reservations')->where('id', $item)->firstOrFail();
       //  dd($data);
        $data->reservations = $data->reservations->filter(function ($value, $key) use ($start_date, $end_date) {
        if ($value->status_id == 3)
           return true;
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
    

    public function guard()
    {
        return Auth::guard();
    }


   


}
