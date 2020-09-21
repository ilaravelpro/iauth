<?php
/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/21/20, 5:56 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;

trait Verify
{
    public function verify(Request $request, $session, $token, $pin)
    {
        list($result, $this->statusMessage) = $this->vendor->verify($request, $session, $token, $pin);
        return $result;
    }
}
