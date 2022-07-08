<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 10:00 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\Methods;

use Carbon\Carbon;
use iLaravel\Core\iApp\Exceptions\iException;
use iLaravel\iAuth\Vendor\AuthBridges\Mobile;
use iLaravel\iAuth\Vendor\AuthBridges\Telegram;
use iLaravel\iAuth\Vendor\AuthSession\GoogleAuthenticator;
use Illuminate\Auth\AuthenticationException;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Exception;
use Illuminate\Support\Facades\Mail;

class Session
{
    public $model, $creator, $type, $resource, $request, $method, $bridges, $session, $sessionModel;

    public function __construct()
    {
        $this->sessionModel = imodal('AuthSession');
    }

    public static function pass(Request $request, $creator, $type, $method, $resource, $model, $session, $callback = null)
    {
        return (new self())->_pass($request, $creator, $type, $method, $resource, $model, $session, $callback);
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
            $ga = GoogleAuthenticator::check($request, $authSession->item(), $pin);
            if (($authSession->item()->role == 'guest' || !in_array($authSession->session, iauth('methods.verify.never', []))) && $bridge = ($ga ? $authSession->bridges()->where('expires_at', '>', Carbon::now())->first() : $authSession->bridges()->where('pin', $pin)->where('expires_at', '>', Carbon::now())->first())) {
                if ($ga){
                    $bridge->pin = $pin;
                    $authSession->meta = array_merge(['google' => true], $authSession->meta ? : []);
                }
                $bridge->verified_at = Carbon::now();
                $bridge->save();
                $authSession->verified = true;
                $authSession->save();
                $result = new $resource($authSession->item());
                list($result, $message) = is_callable($callback) && $callback ? $callback($request, $result, $authSession, $bridge) : [$result, ["The session :method was successfully verified.", ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]]];
                return [$result, $message, $authSession];
            }elseif ($authSession->bridges->count() == 0) {
                $authSession->verified = true;
                $authSession->save();
                $result = new $resource($authSession->item());
                list($result, $message) = is_callable($callback) && $callback ? $callback($request, $result, $authSession, null) : [$result, ["The session :method was successfully verified.", ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]]];
                return [$result, $message, $authSession];
            }
            throw new iException('Code was not found, please resend code or create a new :method session.', ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]);
        }
        throw new iException('Session was not found or has verified, please create a new :method session.', ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]);
    }

    public function _pass(Request $request, $creator, $type, $method, $resource, $model, $session, $callback = null)
    {
        if ($this->sessionModel::where('key', $method)
                ->where('value', $request->input($method))
                ->where('session', $session)
                ->where('expired_at', '>', Carbon::now())->get()->count() > iauth('sessions.expired.count'))
            throw new iException('Your requests exceeded the limit. Please try again in :minutes minutes.', ['minutes' => iauth('sessions.expired.time')]);
        $this->creator = $creator;
        $this->model = $model;
        $this->request = $request;
        $this->method = $method;
        $this->session = $session;
        $this->resource = $resource;
        $this->type = $type;

        $this->session = new $this->sessionModel(['session' => $this->session]);
        $this->session->key = $this->method;
        if (!is_array($this->request->input($this->method)))
            $this->session->value = $this->request->input($this->method);
        $this->session->creator_id = $this->creator->role == 'guest' ? null : $this->creator->id;
        if ($this->creator->role != 'guest' && isset($this->model->id)) {
            $this->session->model = class_name($this->model);
            $this->session->model_id = $this->model->id;
        }elseif ($this->creator->role == 'guest')
            $this->session->model = class_name($this->model);
        return $this->_passed($callback);
    }

    public function _tryPass(Request $request, $session, $token, $resource, $callback = null)
    {
        $this->request = $request;
        if ($this->session = $this->sessionModel::findByToken($session, $token)) {
            $this->creator = $this->session->creator;
            $this->model = $this->session->item();
            $this->method = $this->session->key;
            $this->resource = $resource;
            return $this->_passed($callback, true);
        } else {
            throw new iException('Session was not found or has expired, please create a :method session.', ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]);
        }
    }

    public function _passed($callback, $resend = false)
    {
        $methods = [];
        $this->bridges = Bridge::sort($this->model, $this->session->session, $this->method);
        if (!count($this->bridges))
            throw new AuthenticationException('Not found Verify Method.');
        $this->session->save();
        $field = 'code';
        if ($this->session->item()->role == 'guest' || !in_array($this->session->session, iauth('methods.verify.never', [])) || $this->session->item()->status === 'waiting') {
            if ($this->session->bridgesByMobile()->count() > iauth('bridges.expired.count') || $this->session->bridgesByEmail()->count() > iauth('bridges.expired.count'))
                throw new iException('The confirmation code has been sent to you. Please try again in :minutes minutes.', ['minutes' => iauth('bridges.expired.time')]);
            if (in_array($this->session->item()->username, array_keys(iauth('tester.username')))) {
                if (iauth('tester.username.' . $this->session->item()->username)) {
                    if (in_array('mobile', $this->bridges)) {
                        $bridge = $this->session->bridgesByMobile()->create(['method' => 'test', 'pin' => iauth('tester.username.code')]);
                        $methods[] = 'email';
                    }
                }else {
                    throw new iException('Tester(:tester) is disable.', ['tester'=> $this->session->item()->username]);
                }

            }else {
                if ($this->session->item()->google_authenticator_secret && !$resend) {
                    $bridge = $this->session->bridgesByMobile()->create(['method' => 'google']);
                    $methods[] = 'google';
                }else {
                    if (in_array('mobile', $this->bridges)) {
                        $bridge = $this->session->bridgesByMobile()->create(['method' => 'mobile']);
                        if (function_exists('isms_send'))
                            isms_send("iauth.methods.{$this->session->session}.send.code", $this->session->value, ['code' => $bridge->pin]);
                        $methods[] = 'mobile';
                    }
                    if (in_array('email', $this->bridges) && (($this->session->item()->role != 'guest' && $this->session->item()->email) || filter_var($this->session->value, FILTER_VALIDATE_EMAIL))) {
                        $bridge = $this->session->bridgesByEmail()->create(['method' => 'email']);
                        $mailModel = imodal('Mail\CodeMail');
                        Mail::to([$this->session->item()->role != 'guest' && $this->session->item()->email? $this->session->item()->email->text :$this->session->value])->send(new $mailModel($this->session->session, $this->creator, $this->model, $this->session, $bridge));
                        $methods[] = 'email';
                    }
                }
            }
        } else
            if (in_array('password', $this->bridges) || ($this->session->item()->role != 'guest' && $this->type == 'pass_code')) {
                $methods[] = 'password';
                $field = 'password';
            }
        $result = new $this->resource($this->model, $this->method);
        $result->additional([
            'additional' => [
                'session_key' => $this->session->token,
                'field' => $field,
            ]
        ]);
        if (in_array('password', $methods))
            $message = 'Please enter your password.';
        else
            $message = ['The verification code was sent to your :methods', ["methods" => _t(implode(" & ", $methods))]];
        list($result, $msg, $field) = is_callable($callback) && $callback ? $callback($this->request, $result, $this->session, $methods, $field) : [$result, $message, $field];
        $message = $msg ? : $message;
        return [$result, $message, $this->session, $methods, $field];
    }
}
