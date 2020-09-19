<?php



/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 9:20 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\AuthTheory\Auth;

use Illuminate\Auth\AuthenticationException;

trait Authorized
{
    public function authorized($model)
    {
        $resource = iresource('User');
        $result = new $resource($model);
        if ($result->status != 'active') {
            if (iauth('methods.verify.auto'))
                $model->status = 'active';
            else
                $model->status = 'verified';
            $model->save();
        }
        if (in_array($result->status, ['active', 'verified'])){
            if ($result->status == 'active') {
                $token = $result->createToken('iauth')->accessToken;
                $result->additional(array_merge_recursive($result->additional, [
                    'additional' => ['token' => $token]
                ]));
                return [$result, $token, 'Authorization successfully.'];
            }
            return [$result, null, 'Registration successfully, please wait verification by admin.'];
        }
        throw new AuthenticationException('User is not active.');
    }
}
