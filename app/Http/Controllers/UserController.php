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



class UserController extends Controller
{

 

    /**
     * Registracija
     * @param string $email
     * @param string $password
     * @param file $cv
     * @param string $name
     * @param string $surname
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="/register",
     *     description="Creates a user",
     *     operationId="api.users.create",
     *     produces={"application/json"},
     *     tags={"auth"},
     *     schemes={"http"},
     *     @SWG\Parameter(
	 * 			name="email",
	 * 		in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Username",
      *          @SWG\Schema(ref="#/definitions/String")
	 * 		),
     *    @SWG\Parameter(
	 * 			name="password",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Password",
     *          @SWG\Schema(ref="#/definitions/String")
	 * 		),
     *     @SWG\Parameter(
	 * 			name="cv",
	 * 			in="body",
	 * 			required=false,
	 * 			type="file",
	 * 			description="CV",
      *          @SWG\Schema(ref="#/definitions/String")
	 * 		),
     *    @SWG\Parameter(
	 * 			name="name",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="First name",
      *          @SWG\Schema(ref="#/definitions/String")
	 * 		),
     *    @SWG\Parameter(
	 * 			name="surname",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Last name",
      *          @SWG\Schema(ref="#/definitions/String")
	 * 		),
     *    @SWG\Parameter(
	 * 			name="cv",
	 * 			in="body",
	 * 			required=false,
	 * 			type="File",
	 * 			description="Last name",
      *          @SWG\Schema(ref="#/definitions/File")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="User created"
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Unauthorized action.",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=409,
     *         description="User exists",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid data",
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
         'role_id' => 1   
    ]);
    $user->save();
    
    
     } else {
            return response()->json([
         'error' => 'User with that e-mail address already exists'
     ], 409);
     }
 }



 /**
     * Prijava
     * @param string $email
     * @param string $password
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="/login",
     *     description="Login",
     *     operationId="api.login",
     *     produces={"application/json"},
     *     tags={"auth"},
     *     schemes={"http"},
     *     @SWG\Parameter(
	 * 			name="email",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Korisničko ime za prijavu u sustav",
      *          @SWG\Schema(ref="#/definitions/String")
	 * 		),
     *     @SWG\Parameter(
	 * 			name="password",
	 * 			in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Lozinka",
      *          @SWG\Schema(ref="#/definitions/String")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Login succesfull",
     *       @SWG\Schema(ref="#/definitions/LoginResponse")
     *        
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Unauthorized action.",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="User doesn't exist'",
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
     *     path="/user",
     *     description="Dohvati trenutnog korisnika",
     *     operationId="api.user",
     *     produces={"application/json"},
     *     tags={"user.final"},
     *     schemes={"http"},
     *     @SWG\Parameter(
	 * 			name="authorization",
	 * 		    in="header",
	 * 			required=true,
	 * 			type="string",
	 * 			description="JWT token",
      *          @SWG\Schema(ref="#/definitions/String")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="User recevied",
     *         @SWG\Schema(ref="#/definitions/CurrentUser")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="No data found",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=505,
     *         description="Server error",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
     * )
     */
    public function currentUser(){
        return response()->json($this->guard()->user());
    
  
    }







/**
     * Provjeri da li korisnik postoji
     * @param string $email
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *     path="/users/exist",
     *     description="Check if user exists",
     *     operationId="api.users.exist",
     *     produces={"application/json"},
     *     tags={"user.final"},
     *     schemes={"http"},
     *     @SWG\Parameter(
	 * 			name="email",
	 * 		in="body",
	 * 			required=true,
	 * 			type="string",
	 * 			description="Korisničko ime",
      *          @SWG\Schema(ref="#/definitions/String")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Korisnik postoji"
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="User does not exist",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid data",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     )
     * )
     */
public function exist(Request $request) {
    $data['email'] = $request->input('email');
 
if ($data['email'] == null)
 return response()->json([
'error' => 'Email is required'
], 400);

$check = User::where('email',$data['email'])->count();
if ($check == 0) {
    return response()->json([
        'error' => 'User does not exist'
        ], 400);
} else {
    return response()->json([
        ], 200);
}

}


/**
     * Dohvati listu svih korisnika
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/users",
     *     description="Get list of all users",
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
    public function getUsers(Request $request) {
        if (count($request->query()) == 1) {
            try {
               $key = key($request->query());
               $value = $request->query($key);
               $users = User::where($key, 'like', '%' . $value . '%')->get();
               return response()->json( $users, 200);
            } catch (Illuminate\Database\QueryException $e) {
                return response()->json(['error'=>'Invalid serach data'], 501);
            }
        }
 
    $users = User::all();
    return response()->json($users, 200);
    
    }

    /**
     * Dohvati listu svih korisnika
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/users",
     *     description="Get list of all users",
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
     * Uredi korisnika
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Put(
     *     path="/user/update",
     *     description="Uredi korisnika",
     *     operationId="api.user.edit",
     *     produces={"application/json"},
     *     tags={"user"},
     *     schemes={"http"},
     *     @SWG\Parameter(
	 * 			name="authorization",
	 * 		    in="header",
	 * 			required=true,
	 * 			type="string",
	 * 			description="JWT token",
      *          @SWG\Schema(ref="#/definitions/String")
	 * 		),
        *     @SWG\Parameter(
	 * 			name="account",
	 * 		    in="body",
	 * 			required=false,
	 * 			type="object",
	 * 			description="Account object",
      *          @SWG\Schema(ref="#/definitions/User")
	 * 		),
        *     @SWG\Parameter(
	 * 			name="candidate",
	 * 		    in="body",
	 * 			required=false,
	 * 			type="object",
	 * 			description="Kandidat object",
      *          @SWG\Schema(ref="#/definitions/KNDKandidati")
	 * 		),
     *     @SWG\Response(
     *         response=200,
     *         description="Token",
     *        @SWG\Schema(ref="#/definitions/LoginResponse")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="No data found",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Token absent",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
      *     @SWG\Response(
     *         response=401,
     *         description="Token invalid",
     *         @SWG\Schema(ref="#/definitions/CustomError")
     *     ),
    *     @SWG\Response(
     *         response=505,
     *         description="Server error",
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
    $acc->updated_at = Carbon::now();
    $acc->save();
   
    } catch (Exception $e) {
        dd($e);
         return response()->json(['error' => 'Server error' ],505, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }
    $token = JWTAuth::fromUser($acc);
    return response()->json(['token' => $token, 'user' => $acc ],200, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);    
}



/**
     * Dohvati listu svih korisnika
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/users",
     *     description="Get list of all users",
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
    public function user($id) {
        
 try {
    $users = User::where('id', $id)->firstOrFail();
} catch(NotFound $e) {
    return response()->json(['error' => 'User does not exist'], 404);
}
    return response()->json($users, 200);

    
    }



    public function guard()
    {
        return Auth::guard();
    }


  


   


}
