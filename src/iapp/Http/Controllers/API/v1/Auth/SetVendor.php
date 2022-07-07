<?php

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;

use Illuminate\Auth\AuthenticationException;

trait SetVendor
{
    public function setVendor($request){
        if ($request->{'session'}){
            if (!($model = iauth("sessions.models.{$request->{'session'}}.model")))
                throw new AuthenticationException('Not found your session model.');
            $this->vendor = new $model();
        }
    }
}