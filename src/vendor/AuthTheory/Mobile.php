<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 12:35 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthTheory;

use Illuminate\Http\Request;
use Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class Mobile extends Theory
{
    public function boot(Request $request)
    {
        return $this->pass($request);
    }

    public function passed(Request $request)
    {
        if ($this->model->type == 'verify') {
            $this->model->user->status = 'active';
            $this->model->user->save();
        }
        $this->model->delete();
    }

    public function register(Request $request, $model = null, array $parameters = [])
    {
        $find = $this->theoryModel::where([
            'parent_id' => $model->id,
            ['expired_at', '>', Carbon::now()],
            'theory' => 'mobileCode',
            'user_id' => isset($parameters['verify_id']) ? $parameters['verify_id'] : null,
            'type' => isset($parameters['verify_id']) ? 'verify' : null
        ])->first();
        return $find ?: $this->theoryModel::create([
            'key' => $this->theoryModel::tokenGenerator(),
            'parent_id' => $model->id,
            'value' => config('app.debug') ? 130171 : rand(130171, 999999),
            'theory' => 'mobileCode',
            'expired_at' => Carbon::now()->addMinutes(5),
            'user_id' => isset($parameters['verify_id']) ? $parameters['verify_id'] : null,
            'type' => isset($parameters['verify_id']) ? 'verify' : 'temp'
        ]);
    }

    public function rules(Request $request)
    {
        $model = $this->model;
        return [
            'code' => ['required', function ($key, $value, $fail) use ($model) {
                if ($model->value != $value) {
                    $fail('mobileCode.failed');
                }
            }]
        ];
    }
}
