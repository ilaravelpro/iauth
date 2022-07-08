<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 8:34 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthSession\Auth;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Support\Facades\Hash;

trait Register
{
    public function register(Request $request)
    {
        $user = $this->findUser($request);
        if ($user)
            return $this->response("user duplicated", null, 401);
        $data = [];
        if ($request->input('password')) $data['password'] = Hash::make($request->input('password'));
        $data[$this->username_method] = $request->input($this->username_method);
        $data['role'] = 'user';
        if (isset($this->type) && in_array($this->type, ['code', 'pass_code']))
            $data['status'] = 'watting';
        $register = $this->model::create($data);
        return $register;
    }
}
