<?php

namespace iLaravel\iAuth\Vendor\AuthTheory;

use iLaravel\Core\iApp\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class Auth extends Theory
{
    public function boot(Request $request)
    {
        if (auth()->check()) {

            if ($this->model->parent) {
                $result = $this->model->parent->theory->run($request);
                $this->model->delete();
                return $result;
            }
            return $this->pass($request);
        }

        if (!$this->user_id && !$this->model->trigger) {
            return $this->model;
        }

        if ($this->model->user_id && $this->model->user->status != 'active') {
            return $this->create($request, 'mobileCode', ['verify_id' => $this->model->user_id]);
        }
        return $this->trigger($request);
    }

    public function passed(Request $request)
    {
        if (auth()->check()) {
            return auth()->user();
        }
        return auth()->loginUsingId($this->model->user_id);
    }

    public function register(Request $request, $model, array $parameters = [])
    {
        if (auth()->check() && $model->trigger instanceof Auth) {
            return $model->theory->passed($request);
        }
        if ($model->id && $find = $this->theoryModel::where('parent_id', $model->id)->where('theory', 'auth')->where('expired_at', '>', Carbon::now())->first()) {
            return $find;
        }

        return $this->theoryModel::create([
            'user_id' => isset($parameters['user_id']) ? $parameters['user_id'] : null,
            'theory' => 'auth',
            'type' => 'temp',
            'parent_id' => isset($parameters['user_id']) ? null : $model->id,
            'expired_at' => isset($parameters['user_id']) ? Carbon::now()->addMinutes(1) : Carbon::now()->addMinutes(10),
            'meta' => isset($parameters['meta']) ? $parameters['meta'] : null
        ]);
    }

    public function response()
    {
        if ($this->result instanceof $this->userModel) {
            $auth = app('request')->header('authorization');
            $token = strtolower(substr($auth, 0, 7)) == 'bearer ' ? substr($auth, 7) : $this->result->createToken('api')->accessToken;
            $data = [
                'token' => $token,
            ];
            if (request()->callback && $callback = $this->theoryModel::where('key', request()->callback)->where('expired_at', '>', Carbon::now())->first()) {
                $data['key'] = request()->callback;
                $data['theory'] = $callback->getOriginal('theory');
            }
            return [$this->result, $data];
        } elseif ($this->result instanceof $this->theoryModel && $this->result->getOriginal('theory') == 'auth') {
            return [
                'theory' => 'auth',
                ($this->user_id ? 'key' : 'callback') => $this->result->key
            ];
        }
        return $this->response();
    }

    public function rules(Request $request)
    {
        if (!$this->user_id && !$this->model->getOriginal('trigger') && $this->model->parent) {
            return $this->model->parent->theory->rules($request);
        }
        return [];
    }
}
