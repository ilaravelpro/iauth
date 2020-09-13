<?php


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
