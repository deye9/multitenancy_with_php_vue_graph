<?php

namespace App\Http\Controllers\api;

use JWTAuth;
use JWTFactory;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\RegisterFormRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class AuthController extends Controller
{
    use SendsPasswordResetEmails, ResetsPasswords;
    protected $tag = 'Authentication Controller';

    public function register(RegisterFormRequest $request)
    {
        User::create([
            'name' => $request->json('name'),
            'email' => $request->json('email'),
            'password' => bcrypt($request->json('password')),
        ]);
    }

    public function forgotpassword(Request $request)
    {
        try
        {
            $this->sendResetLinkEmail($request);
        } catch (Exception $e) {
            return $this->error($this::ERROR_RESETTING_USER_PASSWORD);
        }
    }

    public function resetpassword(Request $request)
    {
        try
        {
            \Log::info(5678);
            $this->reset($request);
        } catch (Exception $e) {
            return $this->error($this::ERROR_RESETTING_USER_PASSWORD);
        }
    }
    public function signin(Request $request)
    {
        try {
            $token = JWTAuth::attempt($request->only('email', 'password'), [
                'exp' => Carbon::now()->addWeek()->timestamp,
            ]);
        } catch (JWTException $e) {
            return $this->error($this::USER_LOGIN_ERROR);
        }

        if (!$token) {
            return $this->error($this::USER_LOGIN_ERROR);
        } else {
            $data = [];
            $meta = [];

            $user = User::find($request->user()->id);
            $roles = $user->getRoleNames();
            //request->input('email');

            $meta['token'] = $token;
            $data['name'] = $request->user()->name;
            // $data['permissions'] = gettype($user->getAllPermissions());

            return response()->json([
                'meta' => $meta,
                'access' => $data
            ]);
        }
    }
}