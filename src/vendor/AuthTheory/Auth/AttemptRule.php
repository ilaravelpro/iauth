<?php


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
