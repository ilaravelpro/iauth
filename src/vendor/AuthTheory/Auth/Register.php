<?php


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
        if (!iauth('modes.verify.status'))
            $register->status = 'active';
        $register->role = 'user';
        $register->save();
        return $this->findOrFail($register->serial ?: $register->id);
    }
}
