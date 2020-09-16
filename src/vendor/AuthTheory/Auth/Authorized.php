<?php


namespace iLaravel\iAuth\Vendor\AuthTheory\Auth;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Auth\AuthenticationException;

trait Authorized
{
    public function authorized(Request $request, $session)
    {
        $resource = iresource('User');
        $user = new $resource(auth()->user());
        if ($user->status != 'active')
            throw new AuthenticationException('not active');
        $token = $user->createToken('iauth')->accessToken;
        $user->additional(array_merge_recursive($user->additional, [
            'additional' => ['token' => $token]
        ]));
        $this->statusMessage = 'Authorization successfully.';
        return $user;
    }
}
