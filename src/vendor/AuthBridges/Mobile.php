<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/15/20, 4:13 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthBridges;


class Mobile
{
    public static function send($name, $mobile, $code) {
        try {
            $mobile = \iLaravel\Core\Vendor\Validations\iPhone::parse($mobile);
            \iLaravel\iSMS\Vendor\Service::sendByPatternFast(2,$mobile['number'],["name" => $name ? : '', "code" => $code],'ippanel');
        }catch (\Exception $exception) {}
        return true;
    }
}
