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
	 * 			required=false,
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
     *     )
     * )
     */
    public function reservationRequest(Request $request){
        $me = $this->guard()->user();
        $data['start_date'] = DateTime::createFromFormat('d.m.Y', $request->input('start_date'));
        $data['start_date'] =  $data['start_date']->format('Y-m-d H:i:s');
        if ($request->input('return_date') != null) {
          $data['return_date'] = DateTime::createFromFormat('d.m.Y', $request->input('return_date'));
        } else {
            $data['return_date'] = null;
        }
           $data['item_id'] = json_decode($request->input('item_id'));
             $data['remark'] = $request->input('remark');
       
   if ($data['start_date'] == null)
       return response()->json([
     'error' => 'Start date is required'
 ], 400);
   if ($data['item_id'] == null)
       return response()->json([
     'error' => 'Items are required'
 ], 400);

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
      $reservations = Reservation::with('items.item')->get();
      return response()->json($reservations, 200);
    }



       /**
     * Obriši Rezervaciju
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Delete(
     *     path="reservations/delete/{id}",
     *     description="Obriši rezervaciju sa danim ID-om",
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
     *         description="Korisnik je obrisan"   
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
           $resevation->delete();
           return response()->json();
        } else {
            return response()->json(['error'=>'Not your reservation'], 403);
        }
    
    }




     

    

    public function guard()
    {
        return Auth::guard();
    }


   


}
