<?php
/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/21/20, 5:57 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;

trait Revoke
{
    public function revoke(Request $request, $session, $token = null)
    {
        $this->setVendor($request);
        list($result, $this->statusMessage) = $this->vendor->revoke($request, $session, $token);
        return $result;
    }
}
