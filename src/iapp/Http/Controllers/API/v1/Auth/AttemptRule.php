<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/15/20, 3:29 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Auth\AuthenticationException;

trait AttemptRule
{
    public function attempt_rule(Request $request)
    {
        return [
            $this->username_method => $request->input($this->username_method),
            'password' => $request->input('password')
        ];
    }
}
