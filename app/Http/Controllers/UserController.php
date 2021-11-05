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


    public function update_profile(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();
            if($user) {
                if ($request->has('name')) {
                    $user->name = $request->name;
                }
                if ($request->has('username')) {
                    $user->username = $request->username;
                }

                if ($request->has('avatar')) {
                    $imageName = time() . '.' . $request->avatar->extension();
                    $request->avatar->move(public_path('images'), $imageName);
                    $user->avatar = $imageName;
                }
                $user->save();

                return Response::json([
                    'message' => 'Profile Updated successfully!'
                ], 200);
            }
        }
        catch (\Exception $e) {
            return Response::json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
