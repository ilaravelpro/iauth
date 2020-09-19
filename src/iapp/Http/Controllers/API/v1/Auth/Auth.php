<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 11:44 AM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use iLaravel\iAuth\iApp\Http\Resources\UserSummary;
use iLaravel\iAuth\Vendor\AuthBridge;
use iLaravel\iAuth\Vendor\Methods\Session as SessionMethod;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait Auth
{
    public function auth(Request $request)
    {
        if (!iauth('methods.auth.status')) {
            throw new AuthenticationException('Authorization disabled');
        }
        $this->username_method($request);
        $user = $this->model::where($this->username_method, $request->input($this->username_method))->first();
        if (!in_array($this->username_method, ['username', 'id']) && !($user) && iauth('methods.register.status')) {
            $user = $this->register($request);
        }
        if (iauth('methods.verify.ever') && (iauth('methods.auth.password.status') && !iauth('methods.auth.password.after') ? Hash::check($request->input('password'), $user->password) : true)) {
            list($show, $message) = SessionMethod::pass($request, $this->username_method,UserSummary::class, $user);
            $this->statusMessage = $message;
            return $show;
        } elseif (auth()->attempt($this->attempt_rule($request))) {
            return $this->authorizing($request);
        } else {
            throw new AuthenticationException('Authorization data is not match');
        }

    }

    public function authorizing(Request $request)
    {
        $user = $this->show($request, auth()->user()->serial);
        if ($user->status != 'active')
            throw new AuthenticationException('not active');
        $token = $user->createToken('iauth')->accessToken;
        $user->additional(array_merge_recursive($user->additional, [
            'additional' => ['token' => $token]
        ]));
        $this->statusMessage = 'Authorization successfully.';
        return $user;
    }
}
