<?php

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\AuthSession;

use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Auth\AuthenticationException;

trait Store
{
    public function store(Request $request, $session)
    {
        if (!($model = iauth("sessions.models.{$session}.model")))
            throw new AuthenticationException('Not found your session model.');
        return (new $model())->store($request);
    }

    public function verify(Request $request, $session, $token)
    {

    }

    public function resend(Request $request, $session, $token)
    {

    }
}
