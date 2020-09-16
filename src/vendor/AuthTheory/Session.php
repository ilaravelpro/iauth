<?php

namespace iLaravel\iAuth\Vendor\AuthTheory;


abstract class Session
{
    public $vendor, $resource;
    public function __construct()
    {
        $this->vendor = \iLaravel\iAuth\Vendor\Methods\Session::class;
    }
}
