<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 12:35 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthTheory;

use Illuminate\Http\Request;
use Carbon\Carbon;

class Register extends Theory
{
    public function boot(Request $request)
    {
        return $this->trigger($request);
    }

    public function passed(Request $request)
    {
        $params = $this->model->meta;
        $params['status'] = 'active';
        if ($user = $this->userModel::where('mobile', $this->model->meta['mobile'])->first()) {
            $user->update($params);
        } else {
            $user = $this->userModel::create($params);
        }
        $this->model->delete();
        $auth = $this->theoryModel::create([
            'key' => $user->mobile,
            'user_id' => $user->id,
            'theory' => 'auth',
            'trigger' => config('auth.trigger', 'password'),
        ]);
        return $auth->theory->run($request);
    }

    public function register(Request $request, $model = null, array $parameters = [])
    {
        $parameters['status'] = $this->userModel::count() ? $this->userModel::defaultStatus() : 'active';
        $parameters['type'] = $this->userModel::count() ? $this->userModel::defaultType() : 'admin';
        if (!$this->userModel::where('mobile', $parameters['mobile'])->first()) {
            $this->userModel::create($parameters);
        }
        if ($theory = $this->theoryModel::where('theory', 'register')->where('key', $parameters['mobile'])->first()) {
            $theory->update([
                'trigger' => config('auth.autoActive', false) ? null : config('auth.activeMethod', 'mobileCode'),
                'meta' => $parameters
            ]);
        } else {
            $theory = $this->theoryModel::create([
                'meta' => $parameters,
                'key' => $parameters['mobile'],
                'theory' => 'register',
                'type' => 'chain',
                'trigger' => config('auth.autoActive', false) ? null : config('auth.activeMethod', 'mobileCode'),
                'expired_at' => Carbon::now()->addMinutes(5)
            ]);
        }
        return $theory;
    }

    public function rules(Request $request)
    {
        return [];
    }
}
