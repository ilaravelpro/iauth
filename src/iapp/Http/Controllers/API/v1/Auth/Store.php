<?php
/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/21/20, 5:52 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;

trait Store
{
    public function store(Request $request, $session)
    {
        $this->setVendor($request);
        list($result, $this->statusMessage) = $this->vendor->store($request);
        return $result;
    }
}
