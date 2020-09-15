<?php

namespace iLaravel\iAuth\Vendor\AuthVerify;
use App\Services\Kavenegar;

trait Mobile
{
    public function createMobileVerify($mobile = null)
    {
        $this->bridge = $this->crateBridge('mobile', $mobile ?: $this->user->mobile, 2 * 60);
        if(config('app.env') != 'local')
        {
            $SMS = config('services.sms.model', Kavenegar::class);
            $this->messenger = $SMS::send('verify', $this->bridge);
        }
        return $this;
    }

    public function mobileResetPassword($mobile = null)
    {
        $this->bridge = $this->crateBridge('reset_password', $mobile ?: $this->user->mobile, 2 * 60);
        if (config('app.env') != 'local'){
            $SMS = config('services.sms.model', Kavenegar::class);
            $this->messenger = $SMS::send('reset_password', $this->bridge);
        }
        return $this;
    }
}
