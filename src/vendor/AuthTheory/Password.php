<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 12:35 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthTheory;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Password extends Theory
{
    protected function boot(Request $request)
    {
        if ($this->model->trigger) {
            return $this->trigger($request);
        }
        $user = $this->userModel::find($this->model->parent->user_id);
        $check = Hash::check($request->password, $user->password);
        if (!$check) {
            throw ValidationException::withMessages([
                "password" => __('auth.failed')
            ]);
        }
        return $this->pass($request);
    }

    public function passed(Request $request)
    {
        $this->model->delete();
    }

    public function register(Request $request, $model = null, array $params = [])
    {
        $find = $this->theoryModel::where([
            'parent_id' => $model->id,
            ['expired_at', '>', Carbon::now()],
            'theory' => 'password',
        ])->first();
        return $find ?: $this->theoryModel::create([
            'key' => $this->theoryModel::tokenGenerator(),
            'theory' => 'password',
            'value' => $model->value,
            'parent_id' => $model->id,
            'expired_at' => Carbon::now()->addMinutes(5)
        ]);
    }

    public function rules(Request $request)
    {
        return [
            'password' => 'required|min:6'
        ];
    }
}
