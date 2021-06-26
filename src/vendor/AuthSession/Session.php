<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 6:45 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthSession;


use iLaravel\Core\iApp\Exceptions\iException;
use iLaravel\iAuth\iApp\Http\Resources\UserSummary;
use Illuminate\Auth\AuthenticationException;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;

class Session
{
    public $vendor, $method, $resource, $model, $sessionModel, $sessionResource, $emailModel, $phoneModel, $username_method, $available_methods;
    public $authCheck = false;
    public $type = null;

    public function __construct()
    {
        if ($this->authCheck && !auth()->check())
            throw new AuthenticationException('Please log in.');
        $this->vendor = \iLaravel\iAuth\Vendor\Methods\Session::class;
        $this->sessionModel = imodal('AuthSession');
        $this->sessionResource = iresource('AuthSession') ?: iresource('Resource');
        $this->emailModel = imodal('Email');
        $this->phoneModel = imodal('Phone');
        $this->model = imodal('User');
        $this->type = ipreference("iauth.methods.{$this->method}.type");
    }

    public function store(Request $request, $user = null)
    {
        if ($user = $user ? : $this->findUser($request)) {
            if ($this->available_methods && !in_array($this->username_method, $this->available_methods))
                throw new iException('Your input must be :methods.', ['methods' => implode(' or ', $this->available_methods)]);
            return $this->vendor::pass($request, $user, $this->type, $this->username_method, UserSummary::class, $user, $this->method);
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
            throw new iException(['Session was not found or has revoked.']);
        $authSession->update(['revoked' => 1]);
        return [new $this->sessionResource($authSession), ['The :method session was successfully revoked.', ['method' => _t(ipreference("iauth.sessions.models.{$this->method}.message"))]]];
    }
}
