<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/13/20, 6:47 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;

trait Me
{
    public function me(Request $request)
    {
        $this->statusMessage = 'me';
        $user = $this->show($request, \Auth::user()->serial);
        $user->additional(array_merge_recursive($user->additional, [
            'additional' => [ 'token' => $request->bearerToken() ]
        ]));
        return $user;
    }
}
