<?php

namespace iLaravel\iAuth\iApp\Http\Resources;

use iLaravel\Core\iApp\Http\Resources\Resource;

class AuthSession extends Resource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);
        return $data;
    }
}
