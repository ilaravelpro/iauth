<?php

namespace iLaravel\iAuth\Vendor\AuthTheory;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;

abstract class Theory
{
    protected $model, $caller, $result, $userModel, $theoryModel;

    public function __construct($model)
    {
        $this->model = $model;
        $this->key = $model->key;
        $this->value = $model->value;
        $this->user_id = $model->user_id;
        $this->result = $model;
        $this->userModel = imodal('User');
        $this->theoryModel = imodal('AuthTheory');
    }

    abstract public function register(Request $request, $model, array $parameters = []);

    abstract public function rules(Request $request);

    public function run(Request $request, Theory $caller = null)
    {
        $this->caller = $caller;
        $result = $this->boot($request);
        if ($result instanceof Theory && !($result instanceof static)) {
            return $result;
        } elseif ($result instanceof $this->theoryModel) {
            return $result->theory;
        } elseif ($result != $this) {
            $this->result = $result;
        }
        return $this;
    }

    public function tryPass(Theory $theory, Request $request)
    {
        $this->caller = $theory;
        $result = $this->pass($request);
        if ($result instanceof Theory && $result != $this) {
            return $result;
        } elseif ($this != $result) {
            $this->result = $this;
        }
        return $this;
    }

    public function pass(Request $request)
    {
        $result = $this->passed($request) ?: $this->result;
        if ($this->model->parent && $this->model->parent->expired_at) {
            $this->model->parent->update(['trigger' => null]);
            if ($this->model->parent->type == 'chain') {
                return $this->model->parent->theory->tryPass($this, $request);
            }
            return $this->model->parent->theory;
        } elseif ($this->model->parent) {
            return $this->model->parent->theory->tryPass($this, $request);
        }
        if ($result instanceof Theory && $result != $this) {
            return $result;
        }
        $this->result = $result;
        return $this;
    }

    public function create(Request $request, $theory, array $parameters = [])
    {
        return $this->load($theory)->tryRegister($this, $request, $parameters);
    }

    public function load($theory)
    {
        if (!($plan = config('auth.theories.' . $theory . '.model'))) {
            throw new Exception("$theory Theory not found!");
        }
        return new $plan($this->model);
    }

    public function response()
    {
        if (is_array($this->result)) {
            $result = $this->result;
        } elseif (gettype($this->result) == 'object' && method_exists($this->result, 'toArray')) {
            if ($this->result instanceof $this->theoryModel) {
                $result = $this->result->toArray();
            } else {
                $result = ['data' => $this->result->toArray()];
            }
        } else {
            $result = $this->result;
        }
        if (request()->callback && !isset($result['callback']) && $this->theoryModel::where('key', request()->callback)->where('expired_at', '>', Carbon::now())->count() && request()->callback != $result['key']) {
            $result['callback'] = request()->callback;
        }
        return $result;
    }

    public function tryRegister(Theory $theory, Request $request, array $parameters = [])
    {
        $this->caller = $theory;
        $result = $this->result = $this->register($request, $theory->model, $parameters);
        if (isset($result->trigger)) {
            return $result->trigger->tryRegister($result->theory, $request, $parameters);
        }
        return $this;
    }

    public function trigger(Request $request, array $parameters = [])
    {
        if ($this->model->trigger) {
            return $this->model->trigger->tryRegister($this, $request, $parameters);
        }
        return $this->pass($request);
    }
}
