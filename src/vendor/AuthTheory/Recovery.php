<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 12:35 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthTheory;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Support\Facades\Hash;

class Recovery extends Session
{
    use Auth\Register, Auth\UsernameMethod, Auth\FindUser, Auth\Authorized, Auth\AttemptRule;
    public $method = 'recovery';

    public function verify(Request $request, $session, $token, $pin)
    {
        return $this->vendor::verify($request, $session, $token, $pin, iresource('User'), function ($request, $result, $session, $bridge) {
            $user = $session->item();
            $user->password = Hash::make($request->input('password'));
            $user->save();
            $user->revokeAllTokens();
            $message = null;
            if ($user->status == 'active') {
                list($result, $token, $message) = $this->authorized($user);
                $session->meta = ['passport' => $this->model::findTokenID($token)];
                $session->save();
            }
            return [$result, $message];
        });
    }

    public function rules(Request $request, $action) {
        switch ($action) {
            case 'store':
                return [
                    'username' => 'required',
                ];
                break;
            case 'verify':
                return [
                    'password' => 'required|min:6',
                    'password_confirm' => 'required|same:password',
                ];
                break;
        }
    }
}
