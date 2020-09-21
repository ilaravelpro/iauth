<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 6:45 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthSession;


use iLaravel\iAuth\iApp\Http\Resources\UserSummary;
use Illuminate\Auth\AuthenticationException;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;

class Session
{
    public $vendor, $method, $resource, $model, $sessionModel, $sessionResource, $emailModel, $phoneModel, $username_method;
    public $authCheck = false;

    public function __construct()
    {
        if ($this->authCheck && !auth()->check())
            throw new AuthenticationException('Please log in.');
        $this->vendor = \iLaravel\iAuth\Vendor\Methods\Session::class;
        $this->sessionModel = imodal('IAuthSession');
        $this->sessionResource = iresource('IAuthSession') ?: iresource('Resource');
        $this->emailModel = imodal('Email');
        $this->phoneModel = imodal('Phone');
        $this->model = imodal('User');
    }

    public function store(Request $request, $user = null)
    {
        if ($user = $user ? : $this->findUser($request)) {
            return $this->vendor::pass($request, $this->username_method, UserSummary::class, $user, $this->method);
        } else
            throw new AuthenticationException('User is not found.');
    }

    public function resend(Request $request, $session, $token)
    {
        return $this->vendor::tryPass($request, $session, $token, UserSummary::class);
    }

    public function revoke(Request $request, $session, $token)
    {
        $authSession = $this->sessionModel::where('session', $session);
        $authSession = $authSession->where('token', $token);
        $authSession = $authSession->where('revoked', 0)->first();
        if (!$authSession)
            throw new AuthenticationException('Session was not found or has revoke, please create a session.');
        $authSession->update(['revoked' => 1]);
        return [new $this->sessionResource($authSession), 'The session was successfully revoked.'];
    }
}
