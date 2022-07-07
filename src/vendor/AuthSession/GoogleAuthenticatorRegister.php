<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 12:35 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthSession;

use iAmirNet\GoogleAuthenticator\Authenticator;
use iLaravel\Core\iApp\Exceptions\iException;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use iLaravel\iAuth\iApp\Http\Resources\UserSummary;
use Illuminate\Auth\AuthenticationException;
use Carbon\Carbon;

class GoogleAuthenticatorRegister extends Session
{
    public $method = 'google_authenticator_register';
    public $authCheck = true;
    public $google_auther = null;
    public $username_method = 'secret';

    public function __construct()
    {
        parent::__construct();
        $this->google_auther = new Authenticator();
    }

    public function store(Request $request, $user = null)
    {
        $user = $user ? : auth()->user();
        if (!$user)
            throw new AuthenticationException('User not found.');
        $ga = $this->google_auther->create('iAmirNet', iauth('methods.google_authenticator.title') . " (" . ($user->username ? : ($user->email ? $user->email->text : $user->serial)) .")");
        $request->merge($ga);
        list($result, $message, $session) = parent::store($request, $user);
        $result->additional(array_merge_recursive($result->additional, ['additional' => $ga]));
        return [$result, $message, $session];
    }

    public function verify(Request $request, $session, $token, $pin)
    {
        if ($authSession = $this->sessionModel::findByToken($session, $token)) {
            if (($authSession->item()->role == 'guest' || !in_array($authSession->session, iauth('methods.verify.never', []))) && $lasttime = $this->google_auther->verify($authSession->value, $request->code)) {
                $authSession->verified = true;
                $authSession->meta = ['lasttime' => $lasttime];
                $authSession->save();
                $user = $authSession->item();
                $user->google_authenticator_secret = $authSession->value;
                $user->google_authenticator_last_time = $lasttime;
                $user->save();
                $result = new UserSummary($user);
                $result->additional(array_merge_recursive($result->additional, ['additional' => ['verified' => $lasttime]]));
                list($result, $message) = [$result, ["The session :method was successfully verified.", ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]]];
                return [$result, $message, $authSession];
            }
            throw new iException('Code was not found, please resend code or create a new :method session.', ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]);
        }
        throw new iException('Session was not found or has verified, please create a new :method session.', ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]);
    }

    public function rules(Request $request, $action) {
        switch ($action) {
            case 'sotore' : return [];
            case 'verify':
                return [
                    'code' => "required|numeric|min:6",
                ];
                break;
        }
    }
}
