<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 12:35 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthSession;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use iLaravel\Core\iApp\Http\Controllers\API\v1\Auth\FindUser;
use iLaravel\Core\iApp\Http\Controllers\API\v1\Auth\UsernameMethod;

class LoginPolicy extends Session
{
    public $method = 'login_policy';
    public $authCheck = true;
    public $username_method = 'login_policy';

    public function store(Request $request, $user = null, $mobile = null)
    {
        $request->merge([$this->username_method => ""]);
        $user = $user ? : auth()->user();
        return parent::store($request, $user);
    }

    public function verify(Request $request, $session, $token, $pin)
    {
        return $this->vendor::verify($request, $session, $token, $pin, iresource('User'), function ($request, $result, $session, $bridge) {
            $user = $session->item();
            $user->remote_login_verification = $request->remote_login_verification !== false;
            $user->save();
            $message = _t(':type been set.', ['type' => "Login policy"]);
            return [$result, $message];
        });
    }

    public function rules(Request $request, $action) {
        switch ($action) {
            case 'store':return [];
            case 'verify':
                return [
                    'remote_login_verification' => 'nullable|boolean',
                ];
                break;
        }
    }
}
