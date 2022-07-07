<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 12:35 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthSession;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Auth\AuthenticationException;
use Carbon\Carbon;

class MobileChange extends Session
{
    public $method = 'mobile_change';
    public $authCheck = true;
    public $username_method = 'mobile';

    public function store(Request $request, $user = null, $mobile = null)
    {
        $user = $user ?: auth()->user();
        $mobile = $mobile ?: $request->input('mobile');
        if ($user->mobile && $user->mobile->text == $mobile)
            throw new AuthenticationException('Mobile is equal.');
        return parent::store($request, $user);
    }

    public function verify(Request $request, $session, $token, $pin)
    {
        return $this->vendor::verify($request, $session, $token, $pin, iresource('User'), function ($request, $result, $session, $bridge) {
            $user = $session->item();
            $expire = Carbon::now()->addMinutes(60);
            $user->expired_at_change_mobile = $expire->timestamp;
            $user->save();
            $result->additional(array_merge_recursive($result->additional, [
                'additional' => [
                    'expired_at_format' => $expire->format('Y-m-d H:i:s'),
                    'expired_at_timestamp' => $expire->timestamp,
                ]
            ]));
            return [$result, null];
        });
    }

    public function rules(Request $request, $action)
    {
        switch ($action) {
            case 'store':
                return [
                    'mobile' => "required|numeric",
                ];
                break;
        }
    }
}
