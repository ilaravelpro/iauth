<?php


namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;

use App\User;
use iLaravel\iAuth\iApp\Http\Requests\iLaravel as Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

trait Register
{
    public function register(Request $request)
    {
        $this->username_method($request);
        $user = User::where($this->username_method, $request->input($this->username_method))->first();
        if ($user) {
            return $this->response("user duplicated", null, 401);
        }
        $register = new User;
        $register->password = Hash::make($request->input('password'));
        $register->{$this->username_method} = $request->input($this->username_method);

        if (config('auth.enter.auto_verify')) {
            $register->status = 'active';
        }
        $register->type = 'user';
        $register->save();
        if (config('auth.enter.auto_verify')) {
            return $this->login($request);
        }
        $this->statusMessage = 'registred';
        return $this->show($request, $this->findOrFail($register->serial ?: $register->id));
    }
}
