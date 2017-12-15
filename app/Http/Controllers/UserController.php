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



class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['register', 'login']]);
    }


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
         'role_id' => 2   
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
     * Refreshaj token
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     path="/user/refresh",
     *     description="Napravit refresh tokena",
     *     operationId="api.user.refresh",
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
     * )
     */
   public function refreshToken() {
       
       $token = JWTAuth::getToken();
     if (empty($token)) {
         return response()->json(['error' => 'token_absent'], 400);
     }

    try {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['user not found'], 404);
        }
    } catch (TokenExpired $e) {
        $token = JWTAuth::refresh($token);
        $user = JWTAuth::authenticate($token);
    } catch (TokenInvalid $e) {
        return response()->json(['error' => 'token_invalid'], 401);
    } catch (TokenException $e) {
        return response()->json(['error' => 'token_absent'], 400);
    }  catch (TokenBlacklisted $e) {
        return response()->json(['error' => 'Token on blacklist'], 401);
    }
      return response()->json(['token' => $token, 'user' => $user] ,200, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
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
    public function getUsers() {
        
 
    $users = User::all();
    return response()->json($users, 200);
    
    }

    public function guard()
    {
        return Auth::guard();
    }

   


}
