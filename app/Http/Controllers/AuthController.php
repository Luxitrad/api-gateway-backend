<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use \Illuminate\Database\QueryException;
use App\VerifyUsers;
use Illuminate\Support\Str;
use App\Mail\EmailVerification;
use App\User;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function register(Request $request)
    {
        $userDetails = '';
        if ($request->isJson()) {
            $userDetails = $request->json()->all();
        } else {
            $userDetails = $request->all();
        }

        $validator = Validator::make($userDetails, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $error["message"] = $errors[0];
            $error["code"] = 'VALIDATION_ERROR';
            return response()->json(["error" => $error], 400);
        }
        $userDetails['password'] = bcrypt($userDetails['password']);

        try {
            $user = User::create($userDetails);
            $verifyUser = VerifyUsers::create([
                'user_id' => $user->id,
                'token' => Str::random(40)
            ]);
            Mail::to($user->email)->send(new EmailVerification($user)); // Send Email for account verification
            $success['message'] = "We have sent a confirmation mail to your email. Please check your inbox.";
            return response()->json(['success' => $success], 200);
        } catch (QueryException $exception) {
            return response()->json($exception, 400);
        }
    }
    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|string|email',
    //         'password' => 'required|string',
    //         'remember_me' => 'boolean'
    //     ]);
    //     $credentials = request(['email', 'password']);
    //     if (!Auth::attempt($credentials))
    //         return response()->json([
    //             'message' => 'Unauthorized'
    //         ], 401);
    //     $user = $request->user();
    //     $tokenResult = $user->createToken('Personal Access Token');
    //     $token = $tokenResult->token;
    //     if ($request->remember_me)
    //         $token->expires_at = Carbon::now()->addWeeks(1);
    //     $token->save();
    //     return response()->json([
    //         'access_token' => $tokenResult->accessToken,
    //         'token_type' => 'Bearer',
    //         'expires_at' => Carbon::parse(
    //             $tokenResult->token->expires_at
    //         )->toDateTimeString()
    //     ]);
    // }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
