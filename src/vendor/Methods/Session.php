<?php

namespace iLaravel\iAuth\Vendor\Methods;

use Illuminate\Auth\AuthenticationException;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Exception;

class Session
{
    public $model, $request, $method, $bridges, $session, $sessionModel;

    public function __construct(Request $request, $method = null, $model = null, $session = 'auth')
    {
        $this->sessionModel = imodal('AuthSession');
        $this->model = $model;
        $this->request = $request;
        $this->method = $method;
        $this->session = $session;
    }

    public function boot()
    {
        if ($this->request->input('session_key') && ($session = $this->sessionModel::findByToken($this->session, $this->request->input('session_key')))){
            $this->session = $session;
            $this->model = $this->session->item();
            $this->method = $this->session->key;
        }else{
            $this->session = new $this->sessionModel(['session' => $this->session]);
            $this->session->key = $this->method;
            $this->session->value = $this->request->input($this->method);
            if (isset($this->model->id)) {
                $this->session->model = class_name($this->model);
                $this->session->model_id = $this->model->id;
            }
        }
        $this->bridges = Bridge::sort($this->model, $this->session->session, $this->method);
        return $this->session;
    }

    public static function pass(Request $request, $method, $resource, $model = null, $session = 'auth')
    {
        $self = new self($request, $method, $model, $session);
        list($methods, $session) = $self->_pass();
        $show = new $resource($model, $method);
        $show->additional(['additional' => ['session_key' => $session->token]]);
        return [$show, __('The verification code was sent to your :methods', ["methods" => implode(" & ", $methods)])];
    }

    public function _pass()
    {
        $methods = [];
        $this->boot();
        if (!count($this->bridges))
            throw new AuthenticationException('Not found Verify Method.');
        $this->session->save();
        if ($this->session->bridgesByMobile()->count() > iauth('sessions.expired.count') || $this->session->bridgesByEmail()->count() > iauth('sessions.expired.count'))
            throw new AuthenticationException(__('The confirmation code has been sent to you. Please try again in :minutes minutes.', ['minutes' => iauth('sessions.expired.time')]));
        if (in_array('mobile', $this->bridges)) {
            $this->session->bridgesByMobile()->create(['method' => 'mobile']);
            $methods[] = 'mobile';
        }
        if (in_array('email', $this->bridges)) {
            $this->session->bridgesByEmail()->create(['method' => 'email']);
            $methods[] = 'email';
        }
        return [$methods, $this->session];
    }
}
