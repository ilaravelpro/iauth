<?php

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1;

use iLaravel\Core\iApp\Http\Controllers\API\Methods\Controller\Show;
use iLaravel\Core\iApp\Http\Controllers\API\Controller;
use Illuminate\Http\Request;

class AuthSessionController extends Controller
{
    public $username_method, $vendor;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->model = imodal('User');
        $this->resourceClass = iresource('User');
        $this->vendor = \iLaravel\iAuth\Vendor\Methods\Session::class;
    }
    use Show;

    use AuthSession\Store,
        Auth\Register,
        Auth\Revoke,
        Auth\Me,
        Auth\MeUpdate,
        Auth\AttemptRule,
        Auth\UsernameMethod;
}
