<?php


namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Auth\AuthenticationException;

trait Authorize
{
    public function authorizing(Request $request)
    {
        if (!iauth('modes.authorize.status')) {
            throw new AuthenticationException('Authorize disabled');
        }
        $this->username_method($request);
        if (!in_array($this->username_method, ['username', 'id']) && !$this->model::where($this->username_method, $request->input($this->username_method))->first() && iauth('modes.register')) {
            return $this->register($request);
        }
        if (auth()->attempt($this->attempt_rule($request))) {
            $user = $this->show($request, auth()->user()->serial);
            if ($user->status != 'active')
                throw new AuthenticationException('not active');
            $token = $user->createToken('iauth')->accessToken;
            $user->additional(array_merge_recursive($user->additional, [
                'additional' => ['token' => $token]
            ]));
            $this->statusMessage = 'Authorizing successfully.';
            return $user;
        } else {
            throw new AuthenticationException('Authorize data is not match');
        }
    }
}
