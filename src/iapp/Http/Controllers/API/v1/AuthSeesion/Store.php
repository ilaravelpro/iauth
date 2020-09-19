<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 7:29 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\AuthSession;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Auth\AuthenticationException;

trait Store
{
    public function store(Request $request, $session)
    {
        if (!($model = iauth("sessions.models.{$session}.model")))
            throw new AuthenticationException('Not found your session model.');
        list($result, $message) = (new $model())->store($request);
        $this->statusMessage = $message;
        return $result;
    }

    public function verify(Request $request, $session, $token, $pin)
    {
        if (!($model = iauth("sessions.models.{$session}.model")))
            throw new AuthenticationException('Not found your session model.');
        list($result, $message) = (new $model())->verify($request, $session, $token, $pin);
        $this->statusMessage = $message;
        return $result;
    }

    public function resend(Request $request, $session, $token)
    {
        if (!($model = iauth("sessions.models.{$session}.model")))
            throw new AuthenticationException('Not found your session model.');
        list($result, $message) = (new $model())->resend($request, $session, $token);
        $this->statusMessage = $message;
        return $result;
    }

    public function revoke(Request $request, $session, $token = null)
    {
        if (!($model = iauth("sessions.models.{$session}.model")))
            throw new AuthenticationException('Not found your session model.');
        list($result, $message) = (new $model())->revoke($request, $session, $token);
        $this->statusMessage = $message;
        return $result;
    }
}
