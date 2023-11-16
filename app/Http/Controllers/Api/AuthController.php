<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SignInRequest;
use App\Http\Requests\SignUpRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthController extends Controller
{
    const TOKEN_NAME = 'Personal Access Token';

    /**
     * Handle user registration.
     */
    public function signup(SignUpRequest $request)
    {
        $data = $request->validated();

        $user = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        $user->save();

        $token = $user->createToken(self::TOKEN_NAME)->plainTextToken;

        return response()->json(compact('user', 'token'), ResponseAlias::HTTP_CREATED);
    }

    /**
     * Handle user login.
     */
    public function signin(SignInRequest $request)
    {
        $credentials = $request->validated();
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Provided email or password is incorrect'], ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $token = $user->createToken(self::TOKEN_NAME)->plainTextToken;

        return response()->json(compact('user', 'token'));
    }

    /**
     * Handle user logout.
     */
    public function signout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json('', ResponseAlias::HTTP_NO_CONTENT);
    }
}
