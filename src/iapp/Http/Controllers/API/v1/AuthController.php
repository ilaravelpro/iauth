<?php

namespace iLaravel\Core\iApp\Http\Controllers\API\v1;

use iLaravel\Core\iApp\Http\Controllers\API\Methods\Controller\Show;
use iLaravel\Core\iApp\Http\Controllers\API\v1\Auth\AttemptRule;
use iLaravel\Core\iApp\Http\Controllers\API\v1\Auth\Login;
use iLaravel\Core\iApp\Http\Controllers\API\v1\Auth\Logout;
use iLaravel\Core\iApp\Http\Controllers\API\v1\Auth\Me;
use iLaravel\Core\iApp\Http\Controllers\API\v1\Auth\Register;
use iLaravel\Core\iApp\Http\Controllers\API\v1\Auth\UsernameMethod;

use iLaravel\Core\iApp\Http\Controllers\API\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public $username_method;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->model = imodal('User');
        $this->resourceClass = iresource('User');
    }
    use Show;

    use Login,
        Register,
        Logout,
        Me,
        AttemptRule,
        UsernameMethod;
}
