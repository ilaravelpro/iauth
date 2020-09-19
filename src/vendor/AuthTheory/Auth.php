<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 9:23 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthTheory;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use iLaravel\iAuth\iApp\Http\Resources\UserSummary;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Token;

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
        if (!iauth('methods.auth.status'))
            throw new AuthenticationException('Authorization disabled');
        $this->username_method($request);
        $user = $this->model::where($this->username_method, $request->input($this->username_method))->first();
        if (!in_array($this->username_method, ['username', 'id']) && !($user) && iauth('methods.register.status'))
            $user = $this->register($request);
        if (iauth('methods.auth.password.status') && !iauth('methods.auth.password.after') ? Hash::check($request->input('password'), $user->password) : true) {
            list($result, $message, $session) = $this->vendor::pass($request, $this->username_method, UserSummary::class, $user, 'auth', function ($request, $result, $session, $methods) {
                if (iauth('methods.verify.ever') || $session->item()->status === 'waiting')
                    return [$result, null];
                list($result, $token, $message) = $this->authorized($result->resource);
                if ($result->status == 'active'){
                    $tokenId = (new \Lcobucci\JWT\Parser())->parse($token)->getHeader('jti');
                    $session->meta = ['passport' => $tokenId];
                    $session->save();
                }
                return [$result, $message];
            });
            return [$result, $message];
        } else {
            throw new AuthenticationException('Authorization data is not match');
        }
    }

    public function resend(Request $request, $session, $token)
    {
        return $this->vendor::tryPass($request, $session, $token, UserSummary::class);
    }

    public function verify(Request $request, $session, $token, $pin)
    {
        return $this->vendor::verify($request, $session, $token, $pin, iresource('User'), function ($request, $result, $session, $bridge) {
            list($result, $token, $message) = $this->authorized($session->item());
            if ($result->status == 'active'){
                $tokenId = (new \Lcobucci\JWT\Parser())->parse($token)->getHeader('jti');
                $session->meta = ['passport' => $tokenId];
                $session->save();
            }
            return [$result, $message];
        });
    }

    public function revoke(Request $request, $session, $token = null)
    {
        $bearerToken = $request->bearerToken();
        $authSession = $this->sessionModel::where('session', $session);
        if ($token) {
            $authSession = $authSession->where('token', $token);
        } else {
            $tokenId = (new \Lcobucci\JWT\Parser())->parse($bearerToken)->getHeader('jti');
            $authSession = $authSession->where('meta->passport', $tokenId);
        }
        $authSession = $authSession->where('revoked', 0)->first();
        if (!$authSession)
            throw new AuthenticationException('Session was not found or has revoke, please create a session.');
        $authSession->update(['revoked' => 1]);
        $user = iresource('User');
        $user = new $user($authSession->item());
        if ($access = Token::where('id', $authSession->meta['passport'])->first())
            $access->update(['revoked' => 1]);
        return [$user, 'The session was successfully revoked.'];
    }
}
