<?php


namespace iLaravel\iAuth\Vendor\AuthTheory\Auth;

use Illuminate\Auth\AuthenticationException;

trait Authorized
{
    public function authorized($model)
    {
        $resource = iresource('User');
        $result = new $resource($model);
        if (iauth('methods.verify.auto') || $result->status != 'active')
            $model->status = 'active';
        if ($result->status != 'active')
            throw new AuthenticationException('User is not active.');
        $token = $result->createToken('iauth')->accessToken;
        $result->additional(array_merge_recursive($result->additional, [
            'additional' => ['token' => $token]
        ]));
        $this->statusMessage = 'Authorization successfully.';
        return [$result, $token, 'Authorization successfully.'];
    }
}
