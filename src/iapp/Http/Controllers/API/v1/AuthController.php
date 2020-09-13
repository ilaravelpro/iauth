<?php

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1;

use iLaravel\Core\iApp\Http\Controllers\API\Methods\Controller\Show;
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

    use Auth\Authorize,
        Auth\Register,
        Auth\Revoke,
        Auth\Me,
        Auth\MeUpdate,
        Auth\AttemptRule,
        Auth\UsernameMethod;
}
