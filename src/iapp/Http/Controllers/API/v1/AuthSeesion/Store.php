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
        list($result, $this->statusMessage) = $this->vendor->store($request);
        return $result;
    }

    public function verify(Request $request, $session, $token, $pin)
    {
        list($result, $this->statusMessage) = $this->vendor->verify($request, $session, $token, $pin);
        return $result;
    }

    public function resend(Request $request, $session, $token)
    {
        list($result, $this->statusMessage) = $this->vendor->resend($request, $session, $token);
        return $result;
    }

    public function revoke(Request $request, $session, $token = null)
    {
        list($result, $this->statusMessage) = $this->vendor->revoke($request, $session, $token);
        return $result;
    }

    public function rules(Request $request, $action)
    {
        return $this->vendor->rules($request, $action);
    }
}
