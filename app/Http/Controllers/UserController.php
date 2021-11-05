<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function login (Request $request): \Illuminate\Http\JsonResponse
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth()->attempt($credentials)) {
            $token = auth()->user()->createToken($request->email)->accessToken;
            return Response::json([
                'token' => $token,
                'message' => 'Login Successfully!'
            ], 200);
        } else {
            return Response::json([
                'message' => 'User unauthorized!'
            ], 401);
        }
    }


    public function update_profile(Request $request) {
        try {
            $user = auth()->user();
            if($request->has('name')){
                $user->name = $request->name;
            }
            if($request->has('username')){
                $user->username = $request->username;
            }
            if ($files = $request->file('avatar')) {
                $file = $request->file->store('public/avatars');
                $user->avatar = $file;
            }

            if($request->has('avatar')){
                $imageName = time().'.'.$request->avatar->extension();

                $request->image->move(public_path('images'), $imageName);
            }
        }
        catch (\Exception $e) {
            return Response::json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
