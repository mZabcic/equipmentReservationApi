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
use Excel;
use Carbon\Carbon;



class ItemsController extends Controller
{

  
/**
     * Dohvati iteme ili iteme prema parametrima
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="items",
     *     description="Dohvati sve items ili filtrirane",
     *     operationId="api.items",
     *     produces={"application/json"},
     *     tags={"items"},
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
    public function getAll(Request $request) {
        if (count($request->query()) == 1) {
            try {
               $key = key($request->query());
               $value = $request->query($key);
               $item = Item::with("kit")->with("subtype")->with("type")->with("deviceType")->where($key, '=', $value )->get();
               $item->free = $this->checkStatus($items);
               $item->reservations = null;
               return response()->json($item, 200);
            } catch (Illuminate\Database\QueryException $e) {
                return response()->json(['error'=>'Invalid serach data'], 501);
            }
            }
      $items = Item::with("kit")->with("subtype")->with("type")->with("deviceType")->with("reservations")->get();
   
       foreach ($items as $i) {
        $i->free = $this->checkStatus($i);
        unset($i['reservations']);
       }
      return response()->json($items, 200);
    }


     /**
     * Kreiranje itema
     * @param string $identifier
     * @param string $description
     * @param string $kit_id
     *  * @param string $type_id
     *  * @param string $subtype_id
     *  * @param string $device_type_id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="admin/items/create",
     *     description="Kreiranje itema",
     *     operationId="api.admin.items.create",
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
	 * 			name="identifier",
	 * 		    in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Identifikacijski broj",
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
     *    @SWG\Parameter(
	 * 			name="kit_id",
	 * 			in="body",
	 * 			required=true,
	 * 			type="int",
	 * 			description="Id kit",
     * @SWG\Schema(type="int")
	 * 		),
     *    @SWG\Parameter(
	 * 			name="type_id",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Id vrste",
     *           @SWG\Schema(type="string")
	 * 		),
   *    *    @SWG\Parameter(
	 * 			name="subtype_id",
	 * 			in="body",
	 * 			required=false,
	 * 			type="string",
	 * 			description="Id podvrste",
     *           @SWG\Schema(type="string")
	 * 		),
   *    *    @SWG\Parameter(
	 * 			name="device_type_id",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Id vrste uredaja",
     *           @SWG\Schema(type="string")
	 * 		),
   * *     @SWG\Parameter(
   * 			name="picture",
   * 			in="body",
   * 			required=false,
   * 			type="file",
   * 			description="Slika uređaja",
    *          @SWG\Schema(type="file")
   * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Item created"
     *     ),
     *     @SWG\Response(
     *         response=409,
     *         description="Item with that identifier already exists",
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
$check = Item::where('identifier', $data['identifier'] )->count();
if ($check > 0) {
  return response()->json([
'error' => 'Item with that identifier already exists'
], 409);
}
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



     /**
     * Kreiranje itema iz excel datoteke
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="admin/items/create/file",
     *     description="Kreiranje itema iz excel datoteke",
     *     operationId="api.admin.items.create.file",
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
   * *     @SWG\Parameter(
   * 			name="file",
   * 			in="body",
   * 			required=true,
   * 			type="file",
   * 			description="Excel datoteka",
    *          @SWG\Schema(type="file")
   * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Items and details created"
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
    public function createFromFile(Request $request) {
        $file = $request->file('file');
 if ($file == null)
     return response()->json([
   'error' => 'File is required'
], 400);
$data = Excel::load($file, function($reader) {
    
    })->get();
 
if (count($data) > 1) {
$dataDevice = $data[1]->toArray();
$dataType = $data[2]->toArray();
$dataSubType = $data[3]->toArray();
foreach($dataDevice as $dd)
{
try {
    $device_type_id = DeviceType::where('label', '=', trim($dd['label']))->firstOrFail();
  } catch(NotFound $e) {
    $device_type_id = DB::table('device_types')->insert([
          'label' => trim($dd['label']),
          'description' => trim($dd['description']),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
    ]);
  }
}
foreach($dataType as $dt)
{
  try {
    $type_id = Type::where('label', '=', trim($dt['label']))->firstOrFail();
  } catch(NotFound $e) {
    $device_type_id = DB::table('types')->insert([
          'label' => trim($dt['label']),
          'description' => trim($dt['description']),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
    ]);
  }
}
foreach($dataSubType as $st)
{
  try {
    $subtype_id = Subtype::where('label', '=', trim($st['label']))->firstOrFail();
  } catch(NotFound $e) {
    $subtype_id = DB::table('subtypes')->insert([
          'label' => trim($st['label']),
          'description' => trim($st['description']),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
    ]);
  }
}
}

$data = $data[0]->toArray();


foreach($data as $i)
{
 
try {
  $kit_id = Kit::where('name', '=', trim($i['kit']))->firstOrFail();
} catch(NotFound $e) {
    $kit_id = new Kit;
    $kit_id->name = trim($i['kit']);
    $kit_id->created_at = Carbon::now();
    $kit_id->updated_at = Carbon::now();
    $kit_id->save();
}
try {
  $subtype_id = Subtype::where('label', '=', trim($i['subtype']))->firstOrFail();
} catch(NotFound $e) {
    $subtype_id = Subtype::where('description', '=', trim($i['subtype']))->first();
}
try {
  $type_id = Type::where('label', '=', trim($i['type']))->firstOrFail();
} catch(NotFound $e) {
    $type_id = Type::where('description', '=', trim($i['type']))->first();
}
try {
  $device_type_id = DeviceType::where('label', '=', trim($i['device']))->firstOrFail();
} catch(NotFound $e) {
    $device_type_id = DeviceType::where('description', '=', trim($i['device']))->first();
}
$check = Item::where('identifier',trim($i['identifier']))->count();
if ($check == 0) {
  $item = Item::create([
    'identifier' => trim($i['identifier']),
    'description' =>  trim($i['description']),
     'kit_id' =>  $kit_id == null ? null : $kit_id->id,
     'type_id' => $type_id == null ? null : $type_id->id,
     'subtype_id' => $subtype_id == null ? null : $subtype_id->id,
     'device_type_id' =>  $device_type_id == null ? null : $device_type_id->id
]);
  
}

 
}
}
  

   /**
     * Uredi item
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Put(
     *     path="admin/items/edit",
     *     description="Uredi item, ID itema se šalje u objektu tipa Item",
     *     operationId="api.admin.items.edit",
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
        *     @SWG\Parameter(
	 * 			name="item",
	 * 		    in="body",
	 * 			required=false,
	 * 			type="object",
	 * 			description="Objekt tipa item, sa poljima koja se mijenjaju",
      *          @SWG\Schema(ref="#/definitions/Item")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Uređeni item",
     *        @SWG\Schema(ref="#/definitions/Item")
     *     ),
     *     @SWG\Response(
     *         response=409,
     *         description="Item with that identifier already exists",
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
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid form data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
     * )
     */
    public function edit(Request $request) {
      if ($request->input('item') == null)
      return response()->json([
    'error' => 'Item is required'
  ], 400);
      $item = Item::hydrate([json_decode($request->input('item'))]);
      try {
        $item_id = $item[0]->id;
      } catch (Exception $e) {
        return response()->json([
          'error' => 'Item object must contain ID of an object you want to edit'
      ], 400);
      }
      try {
      $check = Item::where('identifier', $item[0]->identifier )->count();
      if ($check > 0) {
        return response()->json([
     'error' => 'Item with that identifier already exists'
 ], 409);
 }
      } catch (Exception $e) {

      }
      try {
      $itemNew = Item::where("id", "=", $item[0]->id)->firstOrFail();
      $itemNew->identifier = $item[0]->identifier != null ? $item[0]->identifier : $itemNew->identifier;
      $itemNew->description = $item[0]->description!= null ? $item[0]->description : $itemNew->description;
      $itemNew->picture = $item[0]->picture != null ? $item[0]->picture : $itemNew->picture;
      $itemNew->kit_id = $item[0]->kit_id != null ? $item[0]->kit_id : $itemNew->kit_id;
      $itemNew->type_id = $item[0]->type_id != null ? $item[0]->type_id : $itemNew->type_id;
      $itemNew->subtype_id = $item[0]->subtype_id != null ? $item[0]->subtype_id : $itemNew->subtype_id;
      $itemNew->device_type_id = $item[0]->device_type_id != null ? $item[0]->device_type_id : $itemNew->device_type_id;
      $itemNew->working = $item[0]->working != null ? $item[0]->working : $itemNew->working;
      $itemNew->updated_at = Carbon::now();
      $itemNew->save();
     
      } catch (Exception $e) {
           return response()->json(['error' => 'Server error' ],500, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
      }
      return response()->json($itemNew,200, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);    
  }

  
    /**
     * Obriši item
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Delete(
     *     path="admin/items/delete/{id}",
     *     description="Obriši item sa danim ID-om",
     *     operationId="api.admin.items.delete",
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
     *         description="Item je obrisan"   
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
    public function delete($id) {
      try {
      $item = Item::where('id', '=', $id)->firstOrFail();
  } catch (NotFound $e) {
      return response()->json(['error' => 'No item found'], 404);
  }
  $check = Item::with('reservations')->where('id', '=', $id)->count();
  if ($count == 0) {
      $item->delete();
  } else {
    return response()->json(['error' => 'Item has reservations, you cannot delete it'], 407);
  }
  return response()->json();
  
  }


  /**
     * Dohvati status itema, true znaci da je slobodan na danasnji dan, false ako nije
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="items/status/:id",
     *     description="Dohvati status itema",
     *     operationId="api.items.statuses",
     *     produces={"application/json"},
     *     tags={"items"},
     *     @SWG\Response(
     *         response=200,
     *         description="Item free ",
     *  * @SWG\Schema(type="boolean")
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
    public function getStatus($id) {
      $today = Carbon::now();
    $items = Item::with("kit")->with("subtype")->with("type")->with("deviceType")->with('reservations')->where('id', $id)->firstOrFail();
   
    $items->reservations = $items->reservations->filter(function ($value, $key) use ($today) {
      if ($value->status_id != 2)
         return false;
      });
      
       $items->reservations = $items->reservations->filter(function ($value, $key) use ($today) {
          if ($value->returned_date == null) {
              $value->returned_date = '9999-12-31';
          }
         return  $today  <= DateTime::createFromFormat('Y-m-d',$value->start_date) && $today  >= DateTime::createFromFormat('Y-m-d', $value->returned_date);
      });
      dd( $items->reservations);
    if (count($items->reservations) == 0) {
      return response()->json(true, 200);
    }  else 
    return response()->json(false, 200);
  }

 private function checkStatus($items) {
  $today = Carbon::now();
  $items->reservations = $items->reservations->filter(function ($value, $key) use ($today) {
    if ($value->status_id != 2)
       return false;
    });
     $items->reservations = $items->reservations->filter(function ($value, $key) use ($today) {
        if ($value->returned_date == null) {
            $value->returned_date = '9999-12-31';
        }
       return DateTime::createFromFormat('Y-m-d', $today)  <= DateTime::createFromFormat('Y-m-d',$start_date) && DateTime::createFromFormat('Y-m-d', $today)  >= DateTime::createFromFormat('Y-m-d', $end_date);
    });
  if (count($items->reservations) == 0) {
    return true;
  }  else 
  return false;
 }



  /**
     * Item ne radi
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Put(
     *     path="admin/items/broken/{id}",
     *     description="Item ne radi",
     *     operationId="api.admin.items.borken",
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
     *         description="Item je obrisan"   
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
    public function notWorking($id) {
      try {
      $item = Item::where('id', '=', $id)->firstOrFail();
  } catch (NotFound $e) {
      return response()->json(['error' => 'No item found'], 404);
  }
      $item->working = false;
      $item->save();
  return response()->json();
  
  }


  /**
     * Item radi
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Put(
     *     path="admin/items/working/{id}",
     *     description="Item ne radi",
     *     operationId="api.admin.items.working",
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
     *         description="Item radi"   
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
    public function working($id) {
      try {
      $item = Item::where('id', '=', $id)->firstOrFail();
  } catch (NotFound $e) {
      return response()->json(['error' => 'No item found'], 404);
  }
      $item->working = true;
      $item->save();
  return response()->json();
  
  }
  

    public function guard()
    {
        return Auth::guard();
    }


}
