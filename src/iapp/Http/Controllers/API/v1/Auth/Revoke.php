<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/13/20, 6:46 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;

trait Revoke
{
    public function revoke(Request $request)
    {
        $user = $this->show($request, \Auth::user()->serial);
        $request->user('api')->token()->revoke();
        $this->statusMessage = 'Logout successfully.';
        return $user;
    }
}
