<?php


namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

trait Auth
{
    public function auth(Request $request)
    {
        if (!iauth('methods.auth.status')) {
            throw new AuthenticationException('Authorization disabled');
        }
        $this->username_method($request);
        if (!in_array($this->username_method, ['username', 'id']) && !($user = $this->model::where($this->username_method, $request->input($this->username_method))->first()) && iauth('methods.register.status')) {
            return $this->register($request);
        }
        if (iauth('methods.verify.ever') && (iauth('methods.auth.password.status') && !iauth('methods.auth.password.after') ? Hash::check($request->input('password'), $user->password) : true)) {
            $bridges = [];
            if (iauth('methods.verify.mode') == 'smart'){
                $bridges = in_array($this->username_method, ['mobile', 'email']) ? $this->username_method : iauth('methods.verify.other');
            }
            switch (iauth('methods.verify.mode')){
                case 'smart':
                    $bridges = in_array($this->username_method, ['mobile', 'email']) ? $this->username_method : iauth('methods.verify.other');
                    break;
                case 'all':
                    $bridges = in_array($this->username_method, ['mobile', 'email']) ? $this->username_method : iauth('methods.verify.other');
                    break;
            }
            if (in_array('mobile', iauth('methods.verify.theories'))) {

            }
            if (in_array('email', iauth('methods.verify.theories'))) {

            }
        } elseif (auth()->attempt($this->attempt_rule($request))) {
            return $this->authorizing($request);
        } else {
            throw new AuthenticationException('Authorization data is not match');
        }

    }

    public function authorizing(Request $request)
    {
        $user = $this->show($request, auth()->user()->serial);
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
