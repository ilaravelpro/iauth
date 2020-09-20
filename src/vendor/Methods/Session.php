<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 10:00 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\Methods;

use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Exception;

class Session
{
    public $model, $resource, $request, $method, $bridges, $session, $sessionModel;

    public function __construct()
    {
        $this->sessionModel = imodal('IAuthSession');
    }

    public static function pass(Request $request, $method, $resource, $model, $session, $callback = null)
    {
        return (new self())->_pass($request, $method, $resource, $model, $session, $callback);
    }

    public static function tryPass(Request $request, $session, $token, $resource, $callback = null)
    {
        return (new self())->_tryPass($request, $session, $token, $resource, $callback);
    }

    public static function verify(Request $request, $session, $token, $pin, $resource, $callback = null)
    {
        return (new self())->_verify($request, $session, $token, $pin, $resource, $callback);
    }

    public function _verify(Request $request, $session, $token, $pin, $resource, $callback = null)
    {
        if ($authSession = $this->sessionModel::findByToken($session, $token)) {
            if ($bridge = $authSession->bridges()->where('pin', $pin)->where('expires_at', '>', Carbon::now())->first()) {
                $bridge->verified_at = Carbon::now();
                $bridge->save();
                $authSession->verified = true;
                $authSession->save();
                $result = new $resource($authSession->item());
                list($result, $message) = is_callable($callback) && $callback ? $callback($request, $result, $authSession, $bridge) : [$result, 'The session was successfully verified.'];
                return [$result, $message, $authSession];
            }
            throw new AuthenticationException('Bridge was not found, please resend code or create a new session.');
        }
        throw new AuthenticationException('Session was not found or has verified, please create a new session.');
    }

    public function _pass(Request $request, $method, $resource, $model, $session, $callback = null)
    {
        if ($this->sessionModel::where('key', $method)
                ->where('value', $request->input($method))
                ->where('session', $session)
                ->where('expired_at', '>', Carbon::now())->get()->count() > iauth('sessions.expired.count'))
            throw new AuthenticationException(__('The confirmation code has been sent to you. Please try again in :minutes minutes.', ['minutes' => iauth('sessions.expired.time')]));
        $this->model = $model;
        $this->request = $request;
        $this->method = $method;
        $this->session = $session;
        $this->resource = $resource;

        $this->session = new $this->sessionModel(['session' => $this->session]);
        $this->session->key = $this->method;
        $this->session->value = $this->request->input($this->method);
        if (isset($this->model->id)) {
            $this->session->model = class_name($this->model);
            $this->session->model_id = $this->model->id;
        }
        try {
            return $this->_passed($callback);
        } catch (AuthenticationException $e) {
            throw new AuthenticationException('Sorry, the create a session failed, please try again.');
        }
    }

    public function _tryPass(Request $request, $session, $token, $resource, $callback = null)
    {
        $this->request = $request;
        if ($this->session = $this->sessionModel::findByToken($session, $token)) {
            $this->model = $this->session->item();
            $this->method = $this->session->key;
            $this->resource = $resource;
            return $this->_passed($callback);
        } else {
            throw new AuthenticationException('Session was not found or has expired, please create a session.');
        }
    }

    public function _passed($callback)
    {
        $methods = [];
        $this->bridges = Bridge::sort($this->model, $this->session->session, $this->method);
        if (!count($this->bridges))
            throw new AuthenticationException('Not found Verify Method.');
        $this->session->save();
        if ($this->session->bridgesByMobile()->count() > iauth('sessions.expired.count') || $this->session->bridgesByEmail()->count() > iauth('sessions.expired.count'))
            throw new AuthenticationException(__('The confirmation code has been sent to you. Please try again in :minutes minutes.', ['minutes' => iauth('sessions.expired.time')]));
        if (iauth('methods.verify.ever') || $this->session->item()->status === 'waiting') {
            if (in_array('mobile', $this->bridges)) {
                $this->session->bridgesByMobile()->create(['method' => 'mobile']);
                $methods[] = 'mobile';
            }
            if (in_array('email', $this->bridges)) {
                $this->session->bridgesByEmail()->create(['method' => 'email']);
                $methods[] = 'email';
            }
        } else
            if (in_array('password', $this->bridges)) {
                $methods[] = 'password';
            }
        $result = new $this->resource($this->model, $this->method);
        $result->additional(['additional' => ['session_key' => $this->session->token]]);
        $message = __('The verification code was sent to your :methods', ["methods" => implode(" & ", $methods)]);
        list($result, $msg) = is_callable($callback) && $callback ? $callback($this->request, $result, $this->session, $methods) : [$result, $message];
        $message = $msg ? : $message;
        return [$result, $message, $this->session, $methods];
    }
}
