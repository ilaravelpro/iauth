<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 12:35 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthSession;

use iLaravel\Core\iApp\Exceptions\iException;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use iLaravel\Core\iApp\Http\Controllers\API\v1\Auth\FindUser;
use iLaravel\Core\iApp\Http\Controllers\API\v1\Auth\UsernameMethod;

class Any extends Session
{
    public $method = 'any';
    public $authCheck = true;

    public function store(Request $request, $user = null, $mobile = null)
    {
        $this->username_method = $request->input('enter');
        $request->merge([$this->username_method => ""]);
        $user = $user ? : auth()->user();
        $title = iauth("methods.{$this->method}.enters.".$this->username_method .".title", str_replace("_", ' ', ucfirst($this->username_method)));
        Config::set("ilaravel.main.iauth.sessions.models.any.message", $title . " " . ipreference("iauth.sessions.models.any.title"));
        Config::set("ilaravel.main.iauth.sessions.models.any.message", $title . " " . ipreference("iauth.sessions.models.any.message"));
        return parent::store($request, $user);
    }

    public function verify(Request $request, $session, $token, $pin)
    {
        $authSession = $this->sessionModel::findByToken($session, $token);
        if (!$authSession) throw new iException('Session was not found or has verified, please create a new :method session.', ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$authSession->session}.message")))]);

        if (iauth("methods.{$this->method}.enters.".$authSession->key .".external", false))
            return $this->_verify($request, $session, $token, $pin);
        else
            return throw new iException('Not available verify method.');
    }

    public function _verify(Request $request, $session, $token, $pin, $user = null)
    {
        $authSession = $this->sessionModel::findByToken($session, $token);
        if (!$authSession && GoogleAuthenticator::check($request, $user?:auth()->user(),$pin)) {
            $type = iauth("methods.{$this->method}.password.type", 'login');
            $ok = true;
            if ($type == "login")
                $ok = $user->password && Hash::check($request->input('password') ? : $request->input($type.'_password'), $user->password);
            else
                $ok = $user->{$type.'_password'} && Hash::check($request->input($type.'_password'),$user->{$type.'_password'});
            if (!$ok) {
                throw new iException('Please enter the correct :type password.', ['type' => $type]);
            }
            $modalUser = iresource('User');
            return true;
        }

        return $this->vendor::verify($request, $session, $token, $pin, iresource('User'), function ($request, $result, $session, $bridge) {
            $user = $session->item();
            $user->save();
            $message = _t(':type been set.', ['type' => iauth("methods.{$this->method}.enters.".$session->key .".title", str_replace("_", ' ', ucfirst($session->key)))]);
            return [$result, $message];
        });
    }

    public function rules(Request $request, $action) {
        $type = iauth("methods.{$this->method}.password.type", 'login');
        $enters = array_keys(iauth("methods.{$this->method}.enters", []));
        switch ($action) {
            case 'store':
                $rules = [
                    'enter' => "required|string" . (count($enters) ? ("|in:" . implode(",", $enters)) : ""),
                ];
                if (iauth("methods.{$this->method}.password.before") && $type)
                    $rules[$type . '_password'] = 'required|min:6';
                return $rules;
                break;
            case 'verify':
                $second_bridges = iauth('methods.' . $this->method . '.second_bridges', []);
                $rules = [];
                foreach ($second_bridges as $index => $second_bridge) {
                    $rules["{$second_bridge}_code"] = 'required|numeric|min:100000';
                }
                if (iauth("methods.{$this->method}.password.after"))
                    $rules[$type . '_password'] = 'required|min:6';
                return $rules;
                break;
        }
    }
}
