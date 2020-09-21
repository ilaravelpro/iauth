<?php
/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/21/20, 5:56 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;

trait Resend
{
    public function resend(Request $request, $session, $token)
    {
        list($result, $this->statusMessage) = $this->vendor->resend($request, $session, $token);
        return $result;
    }
}
