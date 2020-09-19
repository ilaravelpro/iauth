<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 12:45 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthTheory\Auth;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;

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
