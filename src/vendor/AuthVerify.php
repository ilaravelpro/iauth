<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/15/20, 7:32 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthVerify
{
    use AuthVerify\Mobile;
    protected $user, $messenger, $bridge, $userModel, $bridgeModel;
    public function __construct($user)
    {
        $this->user = $user;
        $this->userModel = imodal('User');
        $this->bridgeModel = imodal('AuthBridge');
    }

    public function createBridge($type, $bridge, $expires_at = 5 * 60)
    {
        $exists = $this->bridgeModel::where([
            'type' => $type,
            'bridge' => $bridge
        ])->first();
        if($exists && time() >= strtotime($exists->expires_at))
        {
            $exists->delete();
        }
        elseif($exists)
        {
            if($exists->user_id !== $this->user->id)
            {
                throw ValidationException::withMessages([
                    'bridge' => __('this bridge is for other user')
                ]);
            }
            return $exists;
        }

        return $this->bridgeModel::create([
            'user_id' => $this->user->id,
            'type' => $type,
            'bridge' => $bridge,
            'expires_at' => Carbon::createFromTimestamp(time() + $expires_at)
        ]);
    }

    public function hasToken($token)
    {
        return $this->bridgeModel::where([
            'token' => $token,
            'user_id' => $this->user->id,
            ['expires_at', '>', Carbon::createFromTimestamp(time())]
        ])
        ->whereNull('verified_at')
        ->first();
    }

    public function hasPin($type, $bridge, $pin)
    {
        return $this->bridgeModel::where([
            'user_id' => $this->user->id,
            'type' => $type,
            'bridge' => $bridge,
            'pin' => $pin,
            ['expires_at', '>', Carbon::createFromTimestamp(time())]
        ])
            ->whereNull('verified_at')
            ->first();
    }

    public function whereTypeBridge($type, $bridge, $verified_at = false)
    {
        $bridge = $this->bridgeModel::where([
            'user_id' => $this->user->id,
            'type' => $type,
            'bridge' => $bridge,
            ['expires_at', '>', Carbon::createFromTimestamp(time())]
        ]);
        if(!$verified_at)
        {
            $bridge->whereNull('verified_at');
        }
        $bridge = $bridge->first();
        if($bridge)
        {
            $this->bridge = $bridge;
        }
        return $bridge;
    }

    public function user()
    {
        return $this->user;
    }

    public function messenger()
    {
        return $this->messenger;
    }

    public function bridge()
    {
        return $this->bridge;
    }
}
