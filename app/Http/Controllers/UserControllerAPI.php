<?php

namespace App\Http\Controllers;

use App\Escola;
use App\Mail\SendMailable;
use App\Notifications\RegistarPassword;
use App\Turma;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Jsonable;

use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

use App\User;
use App\StoreUserRequest;
use DateTime;

define('YOUR_SERVER_URL', 'http://h4a.local/');
// Check "oauth_clients" table for next 2 values:
define('CLIENT_ID', '2');
define('CLIENT_SECRET','9lAzFCSTCUbsnn8WlWYJozLOIdT2givB9TmF03FJ');

class UserControllerAPI extends Controller
{
    public function users(Request $request){
        $users = UserResource::collection(User::all());
        return response()->json([
            'data' => $users,
        ]);
    }

    public function alunos(Request $request){
        $users = UserResource::collection(User::where('tipo','aluno')->get());
        return response()->json([
            'data' => $users,
        ]);
    }

    public function professores(Request $request){
        $users = UserResource::collection(User::where('tipo','professor')->get());
        return response()->json([
            'data' => $users,
        ]);
    }


    public function myProfile(Request $request)
    {
        return  new UserResource($request->user());
    }

    public function login(Request $request){
 		$http = new \GuzzleHttp\Client;

 		if(filter_var($request->email, FILTER_VALIDATE_EMAIL)){
 			$user = User::where('email', $request->email)->first();
 			if($user != null){
                if($user->email_verified_at == '') abort(403, 'Account not verified');
            }else{
                abort(403, 'Unknown user');
            }
        }
        $response = $http->post(YOUR_SERVER_URL.'/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => CLIENT_ID,
                'client_secret' => CLIENT_SECRET,
                'username' => $request->email,
                'password' => $request->password,
                'scope' => ''
            ],
            'exceptions' => false,
        ]);

 		$errorCode= $response->getStatusCode();
 		if ($errorCode=='200') {
 			return json_decode((string) $response->getBody(), true);
 		} else {
 			return response()->json(['message'=>'User credentials are invalid'], $errorCode);
 		}
 	}

 	public function store(Request $request){


 		$validator = Validator::make($request->all(), [
 			'nome' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'tipo' => 'required|in:admin,professor,aluno',
            'escola' => 'nullable',
            'turma' => 'nullable',
 		]);

        if($validator->fails()){
        	return response()->json([
        		'message' => 'Error while registering user'
        	], 400);
        }

        $user = new User([
            'nome' => $request->input('nome'),
            'email' => $request->input('email'),
            'tipo' => $request->input('tipo'),
            'password' => 'secret',
        ]);
        if ($user->tipo == "admin"){
            $user->escola_id = null;
            $user->turma_id = null;

        }elseif ($user->tipo == "aluno"){
            $escola = Escola::where('nome', $request->escola)->first();
            $turma= Turma::where('escola_id', $escola->id)->where('nome', $request->turma)->first();
            $user->escola_id = $escola->id;
            $user->turma_id = $turma->id;
        }else{
            $escola = Escola::where('nome', $request->escola)->first();
            $user->escola_id =$escola->id;
            $user->turma_id = null;
        }

        $user->setRememberToken(Str::random(10));
        $user->save();

        //enviar email
        Mail::to($user->email)->send(new SendMailable($user->name, 'api/users/registarPassword/' . $user->getRememberToken()));

        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
 	}

    public function irParaRegistarPassword(Request $request, $token){
        return redirect()->away(URL::to('/') .'/#/users/registarPassword/' . $token);
    }

    public function getUserByToken(Request $request, $token){
        return User::where('remember_token', $token)->where('password', 'secret')->where('email_verified_at', null)->first();
    }

 	public function logout()
	{
 		Auth::guard('api')->user()->token()->revoke();
 		Auth::guard('api')->user()->token()->delete();
 		return response()->json(['msg'=>'Token revoked'], 200);
	}

	public function registerActivate(Request $request, $token){

		$user = User::where('activation_token', $token)->first();
		if ($user && $request->has('password')) {
	        $user->password = bcrypt($request->input('password'));
	        $user->email_verified_at = date("Y-m-d H:i:s");
	        $user->activation_token = '';
	        $user->save();

	        return response()->json([
	        	'message' => 'Account confirmed'
	        ], 201);
    	}
	}

	public function checkRegisted($token){
		$user = User::where('activation_token', $token)->first();
		if (!$user) {
        	return response()->json([
            	'message' => 'This activation token is invalid.'
        	], 403);
        }
        return response()->json([
        	'message' => 'Valid token'
        ], 200);
	}

    public function changePassword(Request $request){
        if($request->has('email') && $request->has('curPassword') && $request->has('newPassword')){
            $user = User::where('email', $request->input('email'))->first();

            if($user != null){
                if (Auth::id()!= $user->id){
                    return response()->json('You dont have permissions', 500);
                }
                if(Hash::check($request->input('curPassword'), $user->password)){
                    $user->password = bcrypt($request->input('newPassword'));
                    $user->save();
                    return response()->json([
                        'message' => 'Password successfully changed'
                    ], 200);
                }else{
                   return response()->json([
                        'message' => 'Current password incorrect'
                    ], 400);
                }
            }else{
                return response()->json([
                    'message' => `User doesn't exist`
                ], 400);
            }
        }else{
            return response()->json([
                'message' => 'Invalid request'
            ], 400);
        }
    }

    public function editProfile(Request $request){
        if($request->has('username')){
            $user = User::where('username', $request->input('username'))->first();

            if (Auth::id() != $user->id && !Auth::user()->isManager()){
                return response()->json('You dont have permissions', 403);
            }

            if($user != null){
                if($request->has('newUsername') && $request->input('newUsername') != null && $request->input('newUsername') != $user->username){
                    $validator = Validator::make(array('username' => $request->input('newUsername')),[
                        'username' => 'string|unique:users',
                    ]);
                    if($validator->fails()){
                        return response()->json([
                            'message' => 'Invalid new username',
                        ], 400);
                    }
                    $user->username = $request->input('newUsername');
                }
                if($request->has('newName') && $request->input('newName') != null && $request->input('newName') != $user->name){
                    $validator = Validator::make(array('name' => $request->input('newName')),[
                        'name' => 'string',
                    ]);
                    if($validator->fails()){
                        return response()->json([
                            'message' => 'Invalid new name',
                        ], 400);
                    }
                    $user->name = $request->input('newName');
                }
                if($request->has('newPhoto')){
                    if($request->file('newPhoto')){
                        $validator = Validator::make(array('photo' => $request->file('newPhoto')),[
                        'photo' => 'file|mimes:jpeg,bmp,png,jpg',
                        ]);
                        if($validator->fails()){
                            return response()->json([
                                'message' => 'Invalid new photo',
                            ], 400);
                        }
                        $oldPhoto_url = $user->photo_url;
                        $newPhoto_url = str_random(16).'.'.$request->file('newPhoto')->getClientOriginalExtension();
                        $user->photo_url = $newPhoto_url;
                        Storage::disk('public')->delete('profiles/'.$oldPhoto_url);
                        Storage::disk('public')->putFileAs('profiles', $request->file('newPhoto'), $newPhoto_url);
                    }elseif($request->input('newPhoto') == 'removePhoto'){
                        Storage::disk('public')->delete('profiles/'.$user->photo_url);
                        $user->photo_url = null;
                    }
                }
                $user->save();
                return new UserResource($user);
            }else{
                return response()->json([
                    'message' => 'Unknown user',
                ], 400);
            }
        }else{
            return response()->json([
                'message' => 'Bad request',
            ], 400);
        }
    }


    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(null, 204);
    }
}
