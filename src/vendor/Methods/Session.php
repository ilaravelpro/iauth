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
use Illuminate\Support\Facades\Hash;
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

    public static function verify_second(Request $request, $session, $token, $pin, $resource, $callback = null)
    {
        return (new self())->_verify_second($request, $session, $token, $pin, $resource, $callback);
    }

    public function checkPassword($position, $request, $session, $callback = null){
        $ok = true;

        $position_session = $session->session == "any" ? iauth("methods.{$session->session}.enters.".$session->method .".password.{$position}", null) : null;
        $position_session = $position_session ?: iauth("methods.{$session->session}.password.{$position}", 'login');

        if ((!isset($session->meta['new_user']) || (isset($session->meta['new_user']) && !$session->meta['new_user'])) && iauth("methods.{$session->session}.password.{$position}")){
            $type = $session->session == "any" ? iauth("methods.{$session->session}.enters.".$session->method .".password.type", null) : null;
            $type = $type ?: iauth("methods.{$session->session}.password.type", 'login');
            if ($type == "login")
                $ok = Hash::check($request->input('password') ? : $request->input($type.'_password'), $session->item()->password);
            else
                $ok = Hash::check($request->input($type.'_password'), $session->item()->{$type.'_password'});
            if (!$ok) {
                throw new iException('Please enter the correct :type password.', ['type' => $type]);
            }
        }
        return $ok;
    }

    public function _verify(Request $request, $session, $token, $pin, $resource, $callback = null)
    {
        if ($authSession = $this->sessionModel::findByToken($session, $token)) {
            $this->checkPassword('after', $request, $authSession);
            $ga = GoogleAuthenticator::check($request, $authSession->item(), $pin);
            if ((($authSession->item()->role == "guest" || $authSession->item()->status == "waiting") || !in_array($authSession->session, iauth('methods.verify.never', []))) && $bridge = ($ga ? $authSession->bridges()->where('expires_at', '>', Carbon::now())->first() : $authSession->bridges()->where('pin', $pin)->where('expires_at', '>', Carbon::now())->first())) {
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
                if ($log_modal = imodal('Log')) $log_modal::where('model', class_name($this->sessionModel))->where('model_id', $authSession->id)->update(['type_id' => $result->resource->id]);
                request()->merge(['log_model' => class_name($this->sessionModel),'log_model_id' => $authSession->id, 'log_type' => class_name($result->resource),'log_type_id' => $result->resource->id]);
                return [$result, $message, $authSession];
            }elseif ($authSession->bridges->count() == 0) {
                $authSession->verified = true;
                $authSession->save();
                $result = new $resource($authSession->item());
                list($result, $message) = is_callable($callback) && $callback ? $callback($request, $result, $authSession, null) : [$result, ["The session :method was successfully verified.", ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]]];
                if ($log_modal = imodal('Log')) $log_modal::where('model', class_name($this->sessionModel))->where('model_id', $authSession->id)->update(['type_id' => $result->resource->id]);
                request()->merge(['log_model' => class_name($this->sessionModel),'log_model_id' => $authSession->id, 'log_type' => class_name($result->resource),'log_type_id' => $result->resource->id]);
                return [$result, $message, $authSession];
            }
            throw new iException('Code was not found, please resend code or create a new :method session.', ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]);
        }
        throw new iException('Session was not found or has verified, please create a new :method session.', ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]);
    }

    public function _verify_second(Request $request, $session, $token, $pin, $resource, $callback = null)
    {
        if ($authSession = $this->sessionModel::findByToken($session, $token)) {
            $this->checkPassword('after', $request, $authSession);
            $bridges = $authSession->bridges;
            $bridges = (object)($bridges ? $authSession->bridges->groupBy('method')->map(function ($items) {
                return $items->pluck('pin', 'id');
            })->toArray() : []);
            $bridges_verifyed = [];
            if ($bridges->{$authSession->key} && array_search($pin, $bridges->{$authSession->key}) !== false)
                $bridges_verifyed[$authSession->key] = array_search($pin, $bridges->{$authSession->key});
            else throw new iException(ucfirst($authSession->key) . ' Verification Code was not found, please resend code or create a new :method session.', ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]);
            $second_bridges = iauth('methods.' . $authSession->session . '.second_bridges', []);
            foreach ($second_bridges as $second_bridge) {
                if (isset($bridges->$second_bridge)) {
                    $request_code = $request->{$second_bridge . "_code"};
                    if ($bridges->$second_bridge && array_search($request_code, $bridges->$second_bridge) !== false)
                        $bridges_verifyed[$second_bridge] = array_search($request_code, $bridges->$second_bridge);
                    elseif($second_bridge == 'google' && GoogleAuthenticator::check($request, $authSession->item(), $request_code))
                        $bridges_verifyed[$second_bridge] = array_keys($bridges->{$second_bridge})[0];
                    else throw new iException(ucfirst($second_bridge) . ' Verification Code was not found, please resend code or create a new :method session.', ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]);
                }else{
                    throw new iException(ucfirst($second_bridge) . ' Verification Code was not found, please resend code.', ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]);
                }
            }
            $authSession->bridges()->whereIn('id', array_values($bridges_verifyed))->update(['verified_at' => Carbon::now()]);
            $authSession->verified = true;
            $authSession->save();
            $result = new $resource($authSession->item());
            list($result, $message) = is_callable($callback) && $callback ? $callback($request, $result, $authSession, $second_bridges) : [$result, ["The session :method was successfully verified.", ['method'=> ucfirst(_t(ipreference("iauth.sessions.models.{$session}.message")))]]];
            if ($log_modal = imodal('Log')) $log_modal::where('model', class_name($this->sessionModel))->where('model_id', $authSession->id)->update(['type_id' => $result->resource->id]);
            request()->merge(['log_model' => class_name($this->sessionModel),'log_model_id' => $authSession->id, 'log_type' => class_name($result->resource),'log_type_id' => $result->resource->id]);
            return [$result, $message, $authSession];
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
        $this->session = new $this->sessionModel(['session' => $this->session, 'ip' => _get_user_ip()]);
        $this->session->key = $this->method;
        if (!is_array($this->request->input($this->method)))
            $this->session->value = $this->request->input($this->method);
        $this->session->creator_id = $this->creator->role == 'guest' ? null : $this->creator->id;
        if ($this->creator->role != 'guest' && isset($this->model->id)) {
            $this->session->model = class_name($this->model);
            $this->session->model_id = $this->model->id;
        }elseif ($this->creator->role == 'guest')
            $this->session->model = class_name($this->model);
        $this->checkPassword('before', $request, $this->session);
        return $this->_passed($callback);
    }

    public function _tryPass(Request $request, $session, $token, $resource, $callback = null)
    {
        $this->request = $request;
        if ($this->session = $this->sessionModel::findByToken($session, $token, false)) {
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
        $this->bridges = Bridge::sort($this->model, $this->session, $this->session->session, $this->method);
        if ($this->session->session == "any") {
            $enters = iauth("methods.{$this->session->session}.enters", []);
            $this->bridges = isset($enters[$this->method]['bridges']) ? $enters[$this->method]['bridges'] : iauth("methods.{$this->session->session}.second_bridges", $this->bridges);
        }
        if (!iauth('tester.username.' . $this->session->item()->username) && !count($this->bridges))
            throw new AuthenticationException('Not found Verify Method.');
        $this->session->save();
        request()->merge(['log_model' => class_name($this->sessionModel),'log_model_id' => $this->session->id, 'log_type' => $this->session->model,'log_type_id' => $this->model->id]);
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
                $second_bridges = iauth('methods.' . $this->session->session . '.second_bridges', []);
                $second_bridge = in_array($this->request->bridge, $second_bridges) ? $this->request->bridge : null;
                if (!$second_bridge && in_array('google', $this->bridges) && $this->session->item()->google_authenticator_secret && !$resend) {
                    $bridge = $this->session->bridgesByMobile()->create(['method' => 'google']);
                    $methods[] = 'google';
                }else {
                    $mobile_error = false;
                    $send_other_values = isset($enters) && isset($enters[$this->method]['send_other_values']) ? $enters[$this->method]['send_other_values'] : iauth("methods.{$this->session->session}.send_other_values", []);
                    foreach ($send_other_values as $index => $send_other_value) {
                        switch ($send_other_value) {
                            case "request":
                                $send_other_values[$index] = $this->request->$index;
                                break;
                            case "user":
                                $send_other_values[$index] = $this->session->item()->$index;
                                break;
                            case "session":
                                $send_other_values[$index] = $this->session->$index;
                                break;
                            default:
                                $send_other_values[$index] = is_callable($send_other_value) ? $send_other_value($this) : $send_other_value;
                                break;
                        }
                    }
                    if ($second_bridge == 'mobile' || (!$second_bridge && in_array('mobile', $this->bridges))) {
                        try {
                            $bridge = $this->session->bridgesByMobile()->create(['method' => 'mobile']);
                            if (function_exists('isms_send')) {
                                $name_sms = isset($enters) && ((isset($enters[$this->method]['sms']) && $enters[$this->method]['sms']) || !isset($enters[$this->method]['sms'])) ? "{$this->session->session}.{$this->method}" : $this->session->session;
                                $send_other_values = array_filter($send_other_values, 'strlen');
                                isms_send("iauth.methods.$name_sms.send.code", $second_bridge && $this->session->item()->mobile ? $this->session->item()->mobile->text  : $this->session->value, array_merge($send_other_values ? : (isset($this->session->meta) && is_array($this->session->meta) && isset($this->session->meta['send_other_values']) ? $this->session->meta['send_other_values'] : []), ['code' => $bridge->pin]));
                            }
                            $this->session->update(['meta' => array_merge(isset($this->session->meta) && is_array($this->session->meta) ? $this->session->meta : [], ['send_other_values' => $send_other_values])]);
                            $methods[] = 'mobile';
                        }catch (\Throwable $exception) {
                            if ($second_bridge != 'email' || !in_array('email', $this->bridges)) {
                                throw new iException('Please enter a valid mobile number.');
                            }else
                                $mobile_error = true;
                        }
                    }
                    if ($second_bridge == 'email' || (!$second_bridge && in_array('email', $this->bridges)) && (($this->session->item()->role != 'guest' && $this->session->item()->email) || filter_var($this->session->value, FILTER_VALIDATE_EMAIL))) {
                        try {
                            $bridge = $this->session->bridgesByEmail()->create(['method' => 'email']);
                            $mailModel = imodal('Mail\CodeMail');
                            if ($this->session->item()->email|| filter_var($this->session->value, FILTER_VALIDATE_EMAIL)) Mail::to([$this->session->item()->role != 'guest' && $this->session->item()->email? $this->session->item()->email->text :$this->session->value])->send(new $mailModel($this->session->session, $this->session->creator_id > 0 ? $this->session->creator : $this->session->item(), $this->model, $this->session, $bridge));
                            $methods[] = 'email';
                        }catch (\Throwable $exception) {
                            if ($second_bridge != 'mobile' || !in_array('mobile', $this->bridges) || $mobile_error) {
                                throw new iException('Please enter a valid email.');
                            }
                        }
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
        elseif (in_array('google', $methods))
            $message = 'Please check out the Google Authenticator app.';
        else
            $message = ['The verification code was sent to your :methods', ["methods" => _t(implode(" & ", $methods))]];
        list($result, $msg, $field) = is_callable($callback) && $callback ? $callback($this->request, $result, $this->session, $methods, $field) : [$result, $message, $field];
        $message = $msg ? : $message;
        return [$result, $message, $this->session, $methods, $field];
    }
}
