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

class Password extends Session
{
    public $method = 'password';
    public $authCheck = true;
    public $username_method = 'other_password';

    public function store(Request $request, $user = null, $mobile = null)
    {
        $this->username_method = "{$request->type}_password";
        $password = Hash::make($request->input('other_password'));
        $request->merge([$this->username_method => $password]);
        $user = $user ? : auth()->user();
        Config::set("ilaravel.main.iauth.sessions.models.password.message", ucfirst($request->type) . " " . ipreference("iauth.sessions.models.password.title"));
        Config::set("ilaravel.main.iauth.sessions.models.password.message", $request->type . " " . ipreference("iauth.sessions.models.password.message"));
        list($result, $message, $session) = parent::store($request, $user);
        $session->meta = array_merge($session->meta ? : [] ,['type' => $request->type]);
        $session->save();
        return [$result, $message, $session];
    }

    public function verify(Request $request, $session, $token, $pin)
    {
        $this->username_method = "{$request->type}_password";
        return $this->vendor::verify($request, $session, $token, $pin, iresource('User'), function ($request, $result, $session, $bridge) {
            $user = $session->item();
            $user->{$session->meta['type'] == "login" ? "password" :$this->username_method} = Hash::make($request->input($this->username_method));
            $user->save();
            $message = _t(':type Password has been set.', ['type' => ucfirst($session->meta['type'])]);
            return [$result, $message];
        });
    }

    public function rules(Request $request, $action) {
        $types = ipreference("iauth.methods.password.types", []);
        $field = in_array($request->type, $types)? "{$request->type}_password": "other_password";
        switch ($action) {
            case 'store':
                return [
                    $request->type != "login" ? "login_password" : "password" => 'required|min:6',
                    'type' => 'required|string|min:3|in:'. implode(',', $types),
                    $field => 'nullable|confirmed|min:6',
                ];
                break;
            case 'verify':
                return [
                    'type' => 'required|string|min:3|in:'. implode(',', $types),
                    $field => 'required|confirmed|min:6',
                ];
                break;
        }
    }
}
