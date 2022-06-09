<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\API\v1\BaseController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        // password_confirmation === password
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->toArray());
        }

        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        return $this->sendResponse([
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ], 'User created successfully');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->toArray());
        }

        // Check email
        $user = User::query()->where('email', $request->email)->first();

        // Check password
        if(!$user || !Hash::check($request->password, $user->password)) {
            return $this->sendError('Bad creds.', code: 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        return $this->sendResponse([
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ],'Login successfully.');
    }

    /**
     * @return string[]
     */
    public function logout(): array
    {
        auth()->user()?->tokens()->delete();

        return [
            'message' => 'Logged out'
        ];
    }
}
