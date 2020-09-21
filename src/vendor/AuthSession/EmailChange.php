<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 12:35 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthSession;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Auth\AuthenticationException;
use Carbon\Carbon;

class EmailChange extends Session
{
    public $method = 'email_change';
    public $authCheck = true;

    public function store(Request $request, $user = null, $email = null)
    {
        $user = $user ?: auth()->user();
        $email = $email ?: $request->input('email');
        if ($user->email->text != $email)
            throw new AuthenticationException('Email is not equal.');
        return parent::store($request, $user);
    }

    public function verify(Request $request, $session, $token, $pin)
    {
        return $this->vendor::verify($request, $session, $token, $pin, iresource('User'), function ($request, $result, $session, $bridge) {
            $user = $session->item();
            $expire = Carbon::now()->addMinutes(60);
            $user->expired_at_change_email = $expire->timestamp;
            $user->save();
            $result->additional(array_merge_recursive($result->additional, [
                'additional' => [
                    'expired_at_format' => $expire->format('Y-m-d H:i:s'),
                    'expired_at_timestamp' => $expire->timestamp,
                ]
            ]));
            return [$result, null];
        });
    }

    public function rules(Request $request, $action)
    {
        switch ($action) {
            case 'store':
                return [
                    'email' => "required|max:191|regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/",
                ];
                break;
        }
    }
}
