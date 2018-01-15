<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\User;
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
use Carbon\Carbon;


/**
 * @SWG\Swagger(
 *     schemes={"http","https"},
 *     host="http://159.89.108.135",
 *     basePath="/api",
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="Rezervacija resursa",
 *         description="API za login, registraciju i upravljanje korisnicima",
 *         termsOfService="",
 *         @SWG\Contact(
 *             email="mislav.zabcic@fer.hr"
 *         ),
 *         @SWG\License(
 *             name="MIT",
 *             url="https://opensource.org/licenses/MIT"
 *         )
 *     ),
 *     @SWG\ExternalDocumentation(
 *         description="Git wiki",
 *         url="https://github.com/mZabcic/equipmentReservationApi"
 *     )
 * )
 */

class UserController extends Controller
{

 

    /**
     * Registracija
     * @param string $email
     * @param string $password
     * @param string $first_name
     * @param string $last_name
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="auth/register",
     *     description="Registracija",
     *     operationId="api.auth.register",
     *     produces={"application/json"},
     *     tags={"auth"},
     *     schemes={"http"},
     *     @SWG\Parameter(
	 * 			name="email",
	 * 		    in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Email adresa",
     * @SWG\Schema(type="string")
	 * 		),
     *    @SWG\Parameter(
	 * 			name="password",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Lozinka",
     * @SWG\Schema(type="string")
	 * 		),
     *    @SWG\Parameter(
	 * 			name="first_name",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Ime",
     * @SWG\Schema(type="string")
	 * 		),
     *    @SWG\Parameter(
	 * 			name="last_name",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Prezime",
     *           @SWG\Schema(type="string")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="User created"
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
    public function register(Request $request){
            $data['email'] = $request->input('email');
              $data['password'] = $request->input('password');
            
               $data['name'] = $request->input('first_name');
                 $data['surname'] = $request->input('last_name');
           
       if ($data['email'] == null)
           return response()->json([
         'error' => 'Email is required'
     ], 400);
       if ($data['name'] == null)
           return response()->json([
         'error' => 'First name is required'
     ], 400);
       if ($data['surname'] == null)
           return response()->json([
         'error' => 'Last name is required'
     ], 400);
       if ($data['password'] == null)
           return response()->json([
         'message' => 'Password is required'
     ], 400);
     
     
     $check = User::where('email',$data['email'])->count();
                                 
     if ($check == 0) {
     $user = User::create([
        'first_name' => $data['name'],
        'last_name' =>  $data['surname'],
        'password' => bcrypt($data['password']),
         'email' => $data['email'],
         'role_id' => 2,
         'active' => false  
    ]);
    $user->save();
    
    
     } else {
        $check = User::where('email',$data['email'])->firstOrFail();
        if ($check->active == false) {
            $check->delete();
            $user = User::create([
                'first_name' => $data['name'],
                'last_name' =>  $data['surname'],
                'password' => bcrypt($data['password']),
                 'email' => $data['email'],
                 'role_id' => 2,
                 'active' => false  
            ]);
            $user->save();
        } else {
            return response()->json([
         'error' => 'User with that e-mail address already exists'
     ], 409);
    }
     }
 }



/**
     * Prijava
     * @param string $email
     * @param string $password
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="auth/login",
     *     description="Prijava",
     *     operationId="api.auth.login",
     *     produces={"application/json"},
     *     tags={"auth"},
     *     schemes={"http"},
     *     @SWG\Parameter(
	 * 			name="email",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Korisničko ime za prijavu u sustav",
      *         @SWG\Schema(type="string")
	 * 		),
     *     @SWG\Parameter(
	 * 			name="password",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Lozinka",
      *          @SWG\Schema(type="string")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Login succesfull",
     *       @SWG\Schema(ref="#/definitions/LoginResponse")
     *        
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Invalid password",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid form data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="User doesn't exist",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     * @SWG\Response(
     *         response=415,
     *         description="User is not activated",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
*
      *   
     * )
     */
    public function login(Request $request){
        $data['email'] = $request->input('email');
        $data['password'] = $request->input('password');
         if ($data['email'] == null)
       return response()->json([
     'error' => 'Email is required'
 ], 400);
  if ($data['password'] == null)
       return response()->json([
     'error' => 'Password is required'
 ], 400);
 try {
    $user = User::where('email', '=', $data['email'])->firstOrFail();
    
    } catch(NotFound $e) {
        return response()->json(['error' => 'User does not exist'], 404);
    }
   if ($user->active == false ) {
    return response()->json([
        'error' => 'User is not activated'
    ], 415);
   }
    if (Hash::check($data['password'],  $user->password))
{

 $token = JWTAuth::fromUser($user);
  return response()->json(['token' => $token, 'user' => $user], 200);
}
return response()->json(['error' => 'Wrong password'], 401);

       }
 
 
     
    /**
     * Prikaži trenutnog korisnika
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="users/current",
     *     description="Prikaži koji si korisnik",
     *     operationId="api.users.current",
     *     produces={"application/json"},
     *     tags={"users"},
     *     schemes={"http"},
     *     @SWG\Parameter(
	 * 			name="authorization",
	 * 		    in="header",
	 * 			required=true,
	 * 			type="string",
	 * 			description="JWT token",
      *          @SWG\Schema(type="string")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Current user object",
     *         @SWG\Schema(ref="#/definitions/User")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="No data found",
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
     *         response=500,
     *         description="Server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *      @SWG\Response(
     *         response=410,
     *         description="Token expired",
     *         @SWG\Schema(ref="#/definitions/TokenExpired")
     *     )
     * )
     */
    public function currentUser(){
        return response()->json($this->guard()->user());
    
  
    }





/**
     * Dohvati korisnika ili korisnike prema parametrima
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="users",
     *     description="Dohvati sve korisnike ili filtrirane",
     *     operationId="api.users",
     *     produces={"application/json"},
     *     tags={"users"},
     *     schemes={"http"},
     *     @SWG\Response(
     *         response=200,
     *         description="Korisnici" ,
     *       
     *   @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/User"))  
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
    public function getUsers(Request $request) {
        if (count($request->query()) == 1) {
            try {
               $key = key($request->query());
               $value = $request->query($key);
               if ($key != 'id') {
               $users = User::where($key, 'like', '%' . $value . '%')->get();
               } else {
                $users = User::where($key, '=', $value )->get();
               }
               return response()->json( $users, 200);
            } catch (Illuminate\Database\QueryException $e) {
                return response()->json(['error'=>'Invalid serach data'], 501);
            }
        }
 
    $users = User::all();
    return response()->json($users, 200);
    
    }


    /**
     * Obriši korisnika
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Delete(
     *     path="admin/users/delete/{id}",
     *     description="Obriši korisnika sa danim ID-om",
     *     operationId="api.admin.users.delete",
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
     *       @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id korisnika",
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
     *         description="No admin rights",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
*
      *   
     * )
     */
    public function delete($id) {
        try {
        $user = User::where('id', '=', $id)->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No user found'], 404);
    }
        $user->delete();
    return response()->json();
    
    }


    
    /**
     * Obriši sebe
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Delete(
     *     path="/users/delete",
     *     description="Obriši svoj korisnički račun",
     *     operationId="api.users.delete",
     *     produces={"application/json"},
     *     tags={"users"},
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
     *     )
*
      *   
     * )
     */
    public function deleteMe() {
        $me = $this->guard()->user();
        try {
        $user = User::where('id', '=', $me->id)->firstOrFail();
    } catch (NotFound $e) {
        return response()->json(['error' => 'No user found'], 404);
    }
        $user->active = false;
        $user->save();
    return response()->json();
    
    }



   /**
     * Uredi korisnika
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Put(
     *     path="users/edit",
     *     description="Uredi svoj profil",
     *     operationId="api.users.edit",
     *     produces={"application/json"},
     *     tags={"users"},
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
	 * 			name="user",
	 * 		    in="body",
	 * 			required=false,
	 * 			type="object",
	 * 			description="Objekt tipa user, sa poljima koja se mijenjaju",
      *          @SWG\Schema(ref="#/definitions/User")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Token and user",
     *        @SWG\Schema(ref="#/definitions/LoginResponse")
     *     ),
     *     @SWG\Response(
     *         response=409,
     *         description="User with that e-mail address already exists",
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
     *     )
     * )
     */
   public function edit(Request $request) {
    $currentUser = JWTAuth::parseToken()->authenticate();
    $account = User::hydrate([json_decode($request->input('user'))]);
    $check = User::where('email',$account[0]->email )->count();
    if ($check > 0) {
        return response()->json([
     'error' => 'User with that e-mail address already exists'
 ], 409);
 }
    try {
    $acc = User::where("id", "=", $currentUser->id)->first();
    $acc->first_name = $account[0]->first_name != null ? $account[0]->first_name : $acc->first_name;
    $acc->last_name = $account[0]->last_name!= null ? $account[0]->last_name : $acc->last_name;
    $acc->email = $account[0]->email != null ? $account[0]->email : $acc->email;
    $acc->active = $account[0]->active != null ? $account[0]->active : $acc->active;
    $acc->updated_at = Carbon::now();
    $acc->save();
   
    } catch (Exception $e) {
         return response()->json(['error' => 'Server error' ],500, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }
    $token = JWTAuth::fromUser($acc);
    return response()->json(['token' => $token, 'user' => $acc ],200, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);    
}



    /**
     * Dohvati korisnika prema ID-u
     * @param number $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="users/{id}",
     *     description="Dohvati korisnika prema ID-u",
     *     operationId="api.users.id",
     *     produces={"application/json"},
     *     tags={"users"},
     *     schemes={"http"},
     *     @SWG\Response(
     *         response=200,
     *         description="Traženi korisnik"   
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
    public function user($id) {
        
 try {
    $users = User::where('id', $id)->firstOrFail();
} catch(NotFound $e) {
    return response()->json(['error' => 'User does not exist'], 404);
}
    return response()->json($users, 200);

    
    }


      /**
     * Uredi korisnika kao administrator
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Put(
     *     path="admin/users/edit",
     *     description="Uredi korisnika kao administrator",
     *     operationId="api.admin.users.edit",
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
	 * 			name="user",
	 * 		    in="body",
	 * 			required=false,
	 * 			type="object",
	 * 			description="Objekt tipa user, sa poljima koja se mijenjaju",
      *          @SWG\Schema(ref="#/definitions/User")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Edited user",
     *        @SWG\Schema(ref="#/definitions/User")
     *     ),
     *     @SWG\Response(
     *         response=409,
     *         description="User with that e-mail address already exists",
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
   public function editAdmin(Request $request) {
    if ($request->input('user') == null)
    return response()->json([
  'error' => 'User is required'
], 400);
    $user = User::hydrate([json_decode($request->input('user'))]);
    try {
      $user_id = $user[0]->id;
    } catch (Exception $e) {
      return response()->json([
        'error' => 'User object must contain ID of an object you want to edit'
    ], 400);
    }
    
    try {
        $check = User::where('email', $item[0]->email )->count();
        if ($check > 0) {
          return response()->json([
       'error' => 'User with that email already exists'
   ], 409);
   }
        } catch (Exception $e) {
  
        }
    
    $acc = User::where("id", "=", $user[0]->id)->first();
    $acc->first_name = $user[0]->first_name != null ? $user[0]->first_name : $acc->first_name;
    $acc->last_name = $user[0]->last_name!= null ? $user[0]->last_name : $acc->last_name;
    $acc->email = $user[0]->email != null ? $user[0]->email : $acc->email;
    $acc->role_id = $user[0]->role_id != null ? $user[0]->role_id : $acc->role_id;
    $acc->updated_at = Carbon::now();
    $acc->save();
   
    
    return response()->json($acc,200, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);    
}



    public function guard()
    {
        return Auth::guard();
    }


  


   


}
