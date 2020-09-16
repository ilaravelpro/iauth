<?php

namespace iLaravel\iAuth\Vendor\AuthTheory;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use iLaravel\iAuth\iApp\Http\Resources\UserSummary;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class Auth extends Session
{
    use Auth\Register, Auth\UsernameMethod, Auth\Authorized, Auth\AttemptRule;
    protected $username_method, $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = imodal('User');
    }

    public function store(Request $request)
    {
        if (!iauth('methods.auth.status')) {
            throw new AuthenticationException('Authorization disabled');
        }
        $this->username_method($request);
        $user = $this->model::where($this->username_method, $request->input($this->username_method))->first();
        if (!in_array($this->username_method, ['username', 'id']) && !($user) && iauth('methods.register.status')) {
            $user = $this->register($request);
        }
        if (iauth('methods.verify.ever') && (iauth('methods.auth.password.status') && !iauth('methods.auth.password.after') ? Hash::check($request->input('password'), $user->password) : true)) {
            list($show, $message) = $this->vendor::pass($request, $this->username_method, UserSummary::class, $user);
            $this->statusMessage = $message;
            return $show;
        } elseif (auth()->attempt($this->attempt_rule($request))) {
            return $this->authorized($request);
        } else {
            throw new AuthenticationException('Authorization data is not match');
        }
    }
}
