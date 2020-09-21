<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 8:34 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthTheory\Auth;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;

trait FindUser
{
    public function findUser(Request $request)
    {
        $this->username_method($request);
        $user = null;
        if (in_array($this->username_method, ['email', 'mobile'])) {
            if ($this->username_method == 'mobile' && $user = $this->phoneModel::findByMobile($request->input($this->username_method), 'User'))
                $user = $user->item();
            elseif ($this->username_method == 'email' && $user = $this->emailModel::findByEmail($request->input($this->username_method), null, 'User'))
                $user = $user->item();
        } else
            $user = $this->model::where($this->username_method, $request->input($this->username_method))->first();
        return $user;
    }
}
