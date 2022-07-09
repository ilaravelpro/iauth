<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 12:35 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthSession;

use iLaravel\Core\iApp\Exceptions\iException;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use iLaravel\Core\Vendor\iMobile;
use iLaravel\Core\Vendor\Validations\iPhone;
use Illuminate\Auth\AuthenticationException;
use Carbon\Carbon;

class Mobile extends Session
{
    public $method = 'mobile';
    public $authCheck = true;
    public $username_method = 'mobile';

    public function store(Request $request, $user = null, $mobile = null)
    {
        if ($this->phoneModel::findByMobile($mobile ?: $request->input('mobile'), 'User', null, 'mobile', true))
            throw new iException([':field has already been verified or registered for another user.'], ['field' => 'Mobile']);
        $mobile = $mobile ? :  $request->input('mobile');
        if (is_array($mobile)) $mobile = $mobile['full'];
        $request->merge(['mobile' => $mobile]);
        $user = $user ? : auth()->user();
        return parent::store($request, $user);
    }

    public function verify(Request $request, $session, $token, $pin)
    {
        return $this->vendor::verify_second($request, $session, $token, $pin, iresource('User'), function ($request, $result, $session, $bridge) {
            $user = $session->item();
            $imobile = iPhone::parse($session->value);
            $mobile = $session->item()->mobile()->first();
            $mobile->country  = $imobile['country'];
            $mobile->number  = $imobile['number'];
            $mobile->verified_at  = Carbon::now();
            $mobile->save();
            return [$result, null];
        });
    }

    public function rules(Request $request, $action) {
        $type = iauth("methods.mobile.password.type", 'login');
        switch ($action) {
            case 'store':
                $rules = [
                    'mobile' => "required|mobile",
                ];
                if (iauth('methods.mobile.password.before'))
                    $rules[$type . '_password'] = 'required|min:6';
                return $rules;
                break;
            case 'verify':
                $second_bridges = iauth('methods.' . $this->method . '.second_bridges', []);
                $rules = [];
                foreach ($second_bridges as $index => $second_bridge) {
                    $rules["{$second_bridge}_code"] = 'required|numeric|min:100000';
                }
                if (iauth('methods.mobile.password.after'))
                    $rules[$type . '_password'] = 'required|min:6';
                return $rules;
                break;
        }
    }
}
