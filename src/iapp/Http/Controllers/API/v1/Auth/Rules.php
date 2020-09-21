<?php
/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/21/20, 5:58 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;

trait Rules
{
    public function rules(Request $request, $action)
    {
        return $this->vendor->rules($request, $action);
    }
}
