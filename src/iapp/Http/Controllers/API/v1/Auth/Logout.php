<?php


namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;

use iLaravel\iAuth\iApp\Http\Requests\iLaravel as Request;

trait Logout
{
    public function logout(Request $request)
    {
        $user = $this->show($request, \Auth::user()->serial);
        $request->user('api')->token()->revoke();
        $this->statusMessage = 'logout';
        return $user;
    }
}
