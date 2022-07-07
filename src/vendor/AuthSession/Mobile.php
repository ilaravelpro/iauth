<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 12:35 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthSession;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use iLaravel\Core\Vendor\iMobile;
use Illuminate\Auth\AuthenticationException;
use Carbon\Carbon;

class Mobile extends Session
{
    public $method = 'mobile';
    public $authCheck = true;

    public function store(Request $request, $user = null, $mobile = null)
    {
        $mobile = $mobile ? :  $this->phoneModel::findByMobile($request->input('mobile'), null, 'User');
        if ($mobile)
            throw new AuthenticationException('Mobile has already been verified or registered for another user.');
        $user = $user ? : auth()->user();
        if ($user->mobile && $user->mobile->verified_at && (!$user->expired_at_change_mobile || $user->expired_at_change_mobile < Carbon::now()->timestamp))
            throw new AuthenticationException('Mobile change session not found.');
        return parent::store($request, $user); // TODO: Change the autogenerated stub
    }

    public function verify(Request $request, $session, $token, $pin)
    {
        return $this->vendor::verify($request, $session, $token, $pin, iresource('User'), function ($request, $result, $session, $bridge) {
            $user = $session->item();
            $user->expired_at_change_mobile = null;
            $user->save();
            $imobile = iMobile::parse($session->value);
            $mobile = $session->item()->mobile()->first();
            $mobile->country  = $imobile['code'];
            $mobile->number  = $imobile['number'];
            $mobile->verified_at  = Carbon::now();
            $mobile->save();
            return [$result, null];
        });
    }

    public function rules(Request $request, $action) {
        switch ($action) {
            case 'store':
                return [
                    'mobile' => "required|max:191|numeric",
                ];
                break;
        }
    }
}
