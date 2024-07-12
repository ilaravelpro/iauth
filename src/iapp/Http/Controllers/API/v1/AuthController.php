<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/15/20, 2:53 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1;

use iLaravel\Core\iApp\Http\Controllers\API\Methods\Controller\Show;
use iLaravel\Core\iApp\Http\Controllers\API\Controller;
use Illuminate\Auth\AuthenticationException;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;

class AuthController extends Controller
{
    public $username_method, $vendor;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->model = imodal('User');
        $this->resourceClass = iresource('UserAuth', iresource('User'));
    }
    
    use Show;

    use Auth\Store,
        Auth\Resend,
        Auth\Verify,
        Auth\Revoke,
        Auth\Rules,
        Auth\Me,
        Auth\MeUpdate,
        Auth\SetVendor;
}
