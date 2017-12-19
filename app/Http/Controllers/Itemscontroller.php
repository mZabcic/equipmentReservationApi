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



class ItemsController extends Controller
{

  

    public function getAll(Request $request) {
        if (count($request->query()) == 1) {
            try {
               $key = key($request->query());
               $value = $request->query($key);
               $item = Item::with("kit")->with("subtype")->with("type")->with("deviceType")->where($key, '=', $value )->get();
               return response()->json($item, 200);
            } catch (Illuminate\Database\QueryException $e) {
                return response()->json(['error'=>'Invalid serach data'], 501);
            }
            }
      $items = Item::with("kit")->with("subtype")->with("type")->with("deviceType")->get();
      return response()->json($items, 200);
    }


    
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
          'description' => trim($dd['description'])
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
          'description' => trim($dt['description'])
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
          'description' => trim($st['description'])
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
  
  

    public function guard()
    {
        return Auth::guard();
    }


}
