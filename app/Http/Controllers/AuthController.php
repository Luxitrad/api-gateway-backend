<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VerifyUser;
use App\User;
use App\Traits\CustomResponse;
use App\Http\Controllers\Controller;
use App\Mail\EmailVerification;
use App\Mail\VerifyTwoFa;
use App\Notifications\HelloUser;
use Laravel\Passport\HasApiTokens;

use Exception;
use Illuminate\Http\Response;
use \Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\VerifyUsers;
use App\TwoFACodes;

class AuthController extends Controller
{

    use CustomResponse, HasApiTokens;

    public function register(Request $request)
    {
        $userDetails = '';
        if ($request->isJson()) {
            $userDetails = $request->json()->all();
        } else {
            $userDetails = $request->all();
        }
        $validator = Validator::make($userDetails, [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password',
            "country" => 'required',
            "state" => 'required',
            "phone" => 'nullable',
            "address" => 'nullable'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $error["message"] = $errors[0];
            $error["code"] = 'VALIDATION_ERROR';
            return $this->errorMessage(["error" => $error], 400);
        }
        $userDetails['password'] = Hash::make($userDetails['password']);
        try {
            $user = User::create($userDetails);

            $verifyUser = VerifyUsers::create([
                'user_id' => $user->id,
                'token' => Str::random(40)
            ]);
        } catch (QueryException $exception) {
            return $this->errorMessage($exception, 400);
        }

        try {
            $user->notify(new HelloUser());
            // if (Mail::to($user->email)->send(new EmailVerification($user))) {
            //     $success['message'] = "We have sent a confirmation mail to your email. Please check your inbox.";
            //     return $this->validResponse(['success' => $success], Response::HTTP_CREATED);
            // }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }


        $success['message'] = "Please check your inbox.";
        return $this->validResponse(['success' => $success], Response::HTTP_CREATED);
    }

    public function verifyUser($token)
    {
        $error = [];
        $status = "";
        $verifyUser = VerifyUsers::where('token', $token)->first();
        if (isset($verifyUser)) {
            $user = $verifyUser->user;
            if (!$user->isVerified) {
                $verifyUser->user->isVerified = 1;
                $verifyUser->user->save();
                $status = "Your e-mail is verified. You can now login.";
            } else {
                $status = "Your e-mail is already verified. You can now login.";
            }
        } else {
            $error["message"] = "Sorry your email cannot be identified";
            $error["code"] = 'Email Error';
            return $this->errorResponse($error, 422);
        }
        return $this->validResponse(['success' => $status], Response::HTTP_OK);
    }

    // public function login(Request $request)
    // {
    //     if (Auth::attempt(["email" => request("email"), "password" => request("password")])) {
    //         $user = Auth::user();
    //         if (!$user->isVerified) { //if user account is not verified. Request verification.
    //             $verifyToken = $user->verifyUser->token;
    //             Mail::to($user->email)->send(new EmailVerification($user));
    //             $error['message'] = "Your Email is not verified, we have sent a confirmation mail to your email. Please check your inbox.";
    //             $error['code'] = "NOT_VERIFIED";
    //             return response()->json(['error' => $error], 400);
    //         } else {
    //             if ($user["2fa"]) {
    //                 $code = TwoFACodes::create(["user_id" => $user["id"], "code" => substr(uniqid(rand(), true), 16, 7)]);
    //                 $user["code"] = $code["code"];
    //                 Mail::to($user->email)->send(new VerifyTwoFa($user));
    //                 $error['message'] = "Please verify Two-factor Authentication. We have sent a code to your email.";
    //                 $error['code'] = "VERIFY_2FA";
    //                 return response()->json(['error' => $error], 401);
    //             }
    //             $success['user'] = $user;
    //             $success["user"]["token"] = $user->createToken($user->name)->accessToken;
    //             return response()->json(['success' => $success], 200);
    //         }
    //     } else {
    //         $user = User::where('email', $request->email)->first();
    //         if (isset($user)) {
    //             $error['message'] = "The password you have entered is incorrect.";
    //             $error['code'] = "AUTHENTICATION_ERROR";
    //             return response()->json(['error' => $error], 400);
    //         }
    //         $error['message'] = "The email you have entered is incorrect.";
    //         $error['code'] = "AUTHENTICATION_ERROR";
    //         return response()->json(['error' => $error], 400);
    //     }
    // }
}
