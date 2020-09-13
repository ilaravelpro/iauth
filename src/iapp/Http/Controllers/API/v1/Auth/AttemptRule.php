<?php


namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;

use iLaravel\iAuth\iApp\Http\Requests\iLaravel as Request;
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
