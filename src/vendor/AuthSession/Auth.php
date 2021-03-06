<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 9:23 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthSession;

use Carbon\Carbon;
use iLaravel\Core\iApp\Exceptions\iException;
use iLaravel\Core\iApp\Http\Controllers\API\v1\Auth\FindUser;
use iLaravel\Core\iApp\Http\Controllers\API\v1\Auth\UsernameMethod;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use iLaravel\iAuth\iApp\Http\Resources\UserSummary;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Token;

class Auth extends Session
{
    use Auth\Register, UsernameMethod, FindUser, Auth\Authorized, Auth\AttemptRule;
    public $method = 'auth';

    public function store(Request $request, $user = null)
    {
        if (!iauth('methods.auth.status'))
            throw new AuthenticationException('Authorization disabled');
        $user = $this->findUser($request);
        $newUser = false;
        if (!in_array($this->username_method, ['username', 'id']) && !($user) && iauth('methods.register.auto.status'))
            list($user, $newUser) = [$this->register($request), true];
        elseif(!in_array($this->username_method, ['username', 'id']) && !($user) && $this->type == 'pass_code')
            list($user, $newUser) = [$this->model::guest(), true];
        if ($user) {
            list($result, $message, $session) = $this->vendor::pass($request, $user, $this->type, $this->username_method, UserSummary::class, $user, $this->method, function ($request, $result, $session, $methods, $field) {
                if ($this->type == 'pass_code' || !in_array($session->session, iauth('methods.verify.never', [])) || $session->item()->status === 'waiting')
                    return [$result, null, $field];
                if (iauth('methods.auth.password.before') && !Hash::check($request->input('password'), $session->item()->password))
                    throw new AuthenticationException('Authorization data is not match');
                list($result, $token, $message) = $this->authorized($result->resource);
                if ($result->status == 'active') {
                    $session->meta = ['passport' => $this->model::findTokenID($token)];
                }
                $session->verified = true;
                $session->save();
                return [$result, $message, $field];
            });
            $additional = [];
            if (iauth('methods.auth.password.after')) $additional['password_after'] = iauth('methods.auth.password.after');
            if (count($additional)) $result->additional(array_merge_recursive($result->additional, ['additional' => $additional]));
            return [$result, $message];
        } else {
            throw new AuthenticationException('Authorization data is not match');
        }
    }

    public function verify(Request $request, $session, $token, $pin = null)
    {
        if (((!$pin && $this->type == 'pass_code') || iauth('methods.auth.password.after')) && !Hash::check($request->input('password'), $this->sessionModel::findByToken($session, $token)->item()->password)) {
            throw new AuthenticationException('Authorization data is not match');
        }
        $fields = handel_fields([], array_keys($this->rules($request, 'verify')), $request->all());
        return $this->vendor::verify($request, $session, $token, $pin, iresource('User'), function ($request2, $result, $session, $bridge) use ($fields, $request, $pin) {
            if ($pin && $this->type == 'pass_code' && $session->item()->role == 'guest') {
                $data = [];
                foreach ($fields as $value)
                    if (_has_key($request->toArray(), $value))
                        $data = _set_value($data, $value, _get_value($request->toArray(), $value));
                $data['password'] = Hash::make($data['password']);
                unset($data['terms']);
                $userModel = imodal('User');
                $register = $userModel::create($data);
                switch ($session->key) {
                    case 'mobile':
                        $register->saveMobile($session->value, Carbon::now()->format('Y-m-d H:i:s'));
                        $register->saveEmail($data['email'], Carbon::now()->format('Y-m-d H:i:s'));
                        break;
                    case 'email':
                        $register->saveEmail($session->value, Carbon::now()->format('Y-m-d H:i:s'));
                        $register->saveMobile($data['mobile'], Carbon::now()->format('Y-m-d H:i:s'));
                        break;
                }
                $session->creator_id = $register->id;
                $session->model_id = $register->id;
                $session->save();
            }
            list($result, $token, $message) = $this->authorized($session->item());
            if ($result->status == 'active') {
                $session->meta = ['passport' => $this->model::findTokenID($token)];
                $session->save();
            }
            return [$result, $message];
        });
    }

    public function revoke(Request $request, $session = null, $token = null)
    {
        $bearerToken = $request->bearerToken();
        $authSession = $this->sessionModel::where('session', $session);
        if ($token) {
            $authSession = $authSession->where('token', $token);
        } else {
            $authSession = $authSession->where('meta->passport', $this->model::findTokenID($bearerToken));
        }
        $authSession = $authSession->where('revoked', 0)->first();
        $userM = imodal('User');
        if (!$authSession)
            return [$userM::guest(), 'The session was successfully revoked.'];
        $user = iresource('User');
        $authSession->update(['revoked' => 1]);
        $user = iresource('User');
        $user = new $user($authSession->item());
        if ($access = Token::where('id', $authSession->meta['passport'])->first())
            $access->update(['revoked' => 1]);
        return [$user, 'The session was successfully revoked.'];
    }

    public function rules(Request $request, $action)
    {
        switch ($action) {
            case 'store':
                return [
                    'username' => 'required',
                    'password' => iauth('methods.auth.password.before') ? 'required|min:6' : 'nullable',
                ];
                break;
            case 'verify':
                return [
                    'password' => (!$request->pin && $this->type == 'pass_code') || iauth('methods.auth.password.after') ? 'required|min:6' : 'nullable',
                ];
                break;
        }
    }
}
