<?php


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
