<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 6:32 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1;

use iLaravel\Core\iApp\Http\Controllers\API\Methods\Controller\Show;
use iLaravel\Core\iApp\Http\Controllers\API\Controller;
use Illuminate\Auth\AuthenticationException;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;

class AuthSessionController extends Controller
{
    public $username_method, $vendor;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->model = imodal('User');
        $this->resourceClass = iresource('User');
        if (!($model = iauth("sessions.models.{$request->{'session'}}.model")))
            throw new AuthenticationException('Not found your session model.');
        $this->vendor = new $model();
    }
    use AuthSession\Store;

}
