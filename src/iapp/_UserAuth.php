<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/15/20, 2:51 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp;


trait _UserAuth
{
    public function AuthVerify()
    {
        return new \iLaravel\iAuth\Vendor\AuthVerify($this);
    }

    public function createVerify()
    {
        $authVerify = $this->AuthVerify();
        if(!$authVerify->whereTypeBridge('mobile', $this->mobile)){
            return $authVerify->createMobileVerify();
        }
        return $authVerify;
    }

    public function resetPassword()
    {
        $authVerify = $this->AuthVerify();
        if (!$authVerify->whereTypeBridge('reset_password', $this->mobile)) {
            return $authVerify->mobileResetPassword();
        }
        return $authVerify;
    }
}
