<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class UserController extends Controller
{
    public function send_user_reg_link(Request $request): \Illuminate\Http\JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:users',
                'name' => 'required|string|max:50'
            ]);

            if ($validator->fails()) {
                return Response::json([
                    'error' => $validator->messages()
                ], 500);
            }

            // generating unique string
            $now = Carbon::now();
            $unique_code = $now->format('YmdHisu');
            $token = new UserToken();
            $token->unique_code = $unique_code;
            $token->save();

            //generate link
            $link = env('APP_URL').'user/register/'.$unique_code;

            $data = array('name'=>"Demo User", 'link' => $link);
            Mail::send('email/userRegisterEmail', $data, function($message) use ($request) {
                $message->to($request->email, $request->name)->subject
                ('Invite to Register');
                $message->from(env('MAIL_FROM_ADDRESS'),'API Admin');
            });

            return Response::json([
                'message' => 'Email sent successfully!'
            ], 200);
        }
        catch (\Exception $e) {
            return Response::json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // to be removed
    public function register_new_user($token) {
        try {
            //checking token and status
            $token = UserToken::where('unique_code',$token)->where('status','0')->first();
            $submit_api = env('APP_URL').'api/store-user';
            if($token) {
                return View::make('user-register')->with('token',$token->unique_code)->with('submit_api',$submit_api);
            }

            return Response::json([
                'message' => 'Invalid Token'
            ], 500);
        }
        catch (\Exception $e) {
            return Response::json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store_user(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:users',
                'username' => 'required|string|min:3',
                'password' => 'required|confirmed|min:6',
                'token' => 'required|exists:user_tokens,unique_code'
            ]);

            if ($validator->fails()) {
                return Response::json([
                    'error' => $validator->messages()
                ], 500);
            }

            $random_pin = random_int(100000, 999999);

            $user = new User();
            if($request->has('name')) {
                $user->name = $request->name;
            }
            $user->username = $request->username;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->two_factor_secret = $random_pin;
            $user->save();

            $token = auth()->user()->createToken($request->email)->accessToken;

            $link =  env('APP_URL').'api/verify-email/'.$user->email.'/'.$random_pin;

            // sending user 6-digit pin for verification
            $data = array('name'=>$user->name, 'pin' => $random_pin, 'link' => $link);
            Mail::send('email/confirmUserPin', $data, function($message) use ($user) {
                $message->to($user->email, 'User Email Confirmation')->subject
                ('Confirm Registration');
                $message->from(env('MAIL_FROM_ADDRESS'),'API Admin');
            });

            return Response::json([
                'token' => $token,
                'message' => 'Pin Email sent successfully!'
            ], 200);

        }
        catch (\Exception $e) {
            return Response::json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function verify_email($email,$pin): \Illuminate\Http\JsonResponse
    {
        try {
            $user = User::where('email',$email)->where('two_factor_secret',$pin)->first();
            if($user){
                $user->email_verified_at = Carbon::now();
                $user->save();

                return Response::json([
                    'message' => 'User Email Verified Successfully!'
                ], 200);
            } else{
                return Response::json([
                    'message' => 'User Email Not Verified!'
                ], 403);
            }
        }
        catch (\Exception $e) {
            return Response::json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
