<?php


namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;

use iLaravel\iAuth\iApp\Http\Controllers\API\Methods\Controller\Show;
use iLaravel\iAuth\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Auth\AuthenticationException;

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
