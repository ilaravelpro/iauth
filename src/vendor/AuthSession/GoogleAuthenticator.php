<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 12:35 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthSession;

use iAmirNet\GoogleAuthenticator\Authenticator;
use iLaravel\Core\iApp\Exceptions\iException;
use iLaravel\Core\iApp\Http\Controllers\API\v1\Auth\FindUser;
use iLaravel\Core\iApp\Http\Controllers\API\v1\Auth\UsernameMethod;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use iLaravel\iAuth\iApp\Http\Resources\UserSummary;
use Illuminate\Auth\AuthenticationException;

class GoogleAuthenticator extends Session
{
    use UsernameMethod, FindUser;
    public $method = 'google_authenticator';

    public function store(Request $request, $user = null)
    {
        $user = $user ? : (auth()->user() ?:$this->findUser($request));
        if (!$user)
            throw new AuthenticationException('User not found.');
        $lasttime = static::check($request, $user);
        if ($lasttime) {
            $result = new UserSummary($user);
            $result->additional(array_merge_recursive($result->additional, ['additional' => ['verified' => $lasttime]]));
            list($result, $message) = [$result, ["The session :method was successfully verified.", ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$this->method}.message")))]]];
            return [$result, $message];
        }else {
            throw new iException('Code was not found');
        }
    }

    public static function check(Request $request, $user, $code = null) {
        if (!$user->google_authenticator_secret || !($code ?:$request->code)) return false;
        $lasttime = Authenticator::verified($user->google_authenticator_secret, $code ?:$request->code, 'enabled', $user->google_authenticator_last_time);
        if ($lasttime) {
            $user->google_authenticator_last_time = $lasttime;
            $user->save();
            return $lasttime;
        }else {
            false;
        }
    }

    public function verify(Request $request, $session, $token, $pin)
    {
        throw new iException('Not available verify method.');
    }

    public function rules(Request $request, $action) {
        switch ($action) {
            case 'store':
                return [
                        'code' => "required|numeric|min:6"
                ];
                break;
        }
    }
}
