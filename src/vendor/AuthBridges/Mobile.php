<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/15/20, 4:13 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthBridges;

use iLaravel\Core\Vendor\iMobile;

class Mobile
{
    public static function send($name, $mobile, $code) {
        $mobile = iMobile::parse($mobile);
        \iLaravel\iSMS\Vendor\Service::sendByPatternFast(2,$mobile['number'],["name" => $name, "code" => $code],'ippanel');
        return true;
    }
}
