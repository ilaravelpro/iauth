<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 6:45 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthTheory;


abstract class Session
{
    public $vendor, $resource, $sessionModel;
    public function __construct()
    {
        $this->vendor = \iLaravel\iAuth\Vendor\Methods\Session::class;
        $this->sessionModel = imodal('AuthSession');
    }
}
