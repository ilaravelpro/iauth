<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 8:34 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthTheory\Auth;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Support\Facades\Hash;

trait Register
{
    public function register(Request $request)
    {
        $this->username_method($request);
        $user = $this->model::where($this->username_method, $request->input($this->username_method))->first();
        if ($user)
            return $this->response("user duplicated", null, 401);
        $register = new $this->model;
        $register->password = Hash::make($request->input('password'));
        $register->{$this->username_method} = $request->input($this->username_method);
        $register->role = 'user';
        $register->save();
        return $register;
    }
}
