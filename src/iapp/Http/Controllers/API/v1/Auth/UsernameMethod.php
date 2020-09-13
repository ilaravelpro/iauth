<?php


namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;

use iLaravel\iAuth\iApp\Http\Requests\iLaravel as Request;

trait UsernameMethod
{
    public function username_method(Request $request)
    {
        if ($this->username_method) return $this->username_method;
        $username = $request->input('username');
        $type = 'username';
        if (ctype_digit($username)) {
            $type = 'mobile';
            $request->request->remove('username');;
            $request->merge([$type => $username]);
        } elseif (strpos($username, '@')) {
            $type = 'email';
            $request->request->remove('username');
            $request->merge([$type => $username]);
        }
        $this->username_method = $type;
        return $type;
    }
}
