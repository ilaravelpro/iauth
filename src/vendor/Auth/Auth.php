<?php

namespace App\Http\Controllers\API\Users;

use App\Requests\Maravel as Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\UserSummary;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Token;

trait Auth {

    public function login(Request $request)
    {
        $user = User::where($request->method, $request->{$request->method})->first();
        $request->validate([
            $request->method => 'required|exists:users'
        ]);
        if ($user->status != 'active') {
            throw ValidationException::withMessages([
                $request->original_method => __('auth.inactive')
            ]);
        }
        $this->statusMessage = 'succsess';
        $cache = Str::random(110);
        Cache::put($cache, json_encode([
            'user' => $user->serial,
            'input' => 'password'
        ]), 60 * 2);
        $this->incrementLoginAttempts($request);
        return [
            'user' => new UserSummary($user, $request->method),
            'input' => 'password',
            'url' => route('api.loginKey', $cache),
            'expires_at' => time() + (60 *2)
        ];
    }

    public function loginKey(Request $request, $key)
    {
        $parse = Cache::getJson($key);
        if($parse->input == 'password')
        {
            $request->validate($this->rules($request, '_password'));
            $user_id = User::encode_id($parse->user);
            $user = auth()->attempt(['id' => $user_id, 'password' => $request->password]);
            if(!$user)
            {
                throw ValidationException::withMessages([
                    "password" => __('auth.failed')
                ]);
            }

            $user = $this->show($request, auth()->user());
            $token = $this->createToken($request, auth()->user());
            $user->additional(array_merge_recursive($user->additional, [
                'token' => $token
            ]));
            $this->statusMessage = 'succsess';
            Cache::forget($key);
            return $user;
        }
        throw ValidationException::withMessages([
            "input" => __('failed')
        ]);
    }

    public function createToken($request, $user, array $scopes = [], array $meta = [])
    {
        $token = $user->createToken('api', $scopes);
        if(!empty($meta))
        {
            $model = $token->toArray()['token'];
            $model->meta = array_merge_recursive($model->meta ?: [], $meta);
            $model->save();
        }
        return $token->accessToken;
    }

    public function register(Request $request)
    {
        $this->incrementRegisterAttempts($request);
        $user = $this->store($request);
        if($request->status == 'awaiting')
        {
            $user->resource->createVerify();
        }
        if($request->status != 'active'){
            $cache = Str::random(110);
            Cache::put($cache, json_encode([
                'user' => $user->serial,
                'input' => 'pin'
            ]), 60 * 2);
            $user->additional(array_merge_recursive(
                $user->additional, [
                    'user' => new UserSummary($user->resource, 'mobile'),
                    'input' => 'pin',
                    'url' => route('api.auth.verify', $cache),
                    'expires_at' => time() + (60 * 2)
                ]
            ));
        }
        $this->statusMessage = 'succsess';
        return $user;
    }

    public function verify(Request $request, $key)
    {
        $parse = Cache::getJson($key);
        if ($parse->input == 'pin') {
            $user = User::find(User::encode_id($parse->user));
            if (!$user) {
                throw ValidationException::withMessages([
                    "pin" => __('auth.failed')
                ]);
            }

            if ($user->status != 'awaiting') {
                throw ValidationException::withMessages([
                    $request->original_method => 'not.awaiting'
                ]);
            }
            $trust = $user->AuthVerify()->hasPin('mobile', $user->mobile, $request->pin);
            if ($trust) {
                Cache::forget($key);
                $trust->verify();
                $this->statusMessage = 'success';
                return $this->show($request, User::find($user->id));
            }
            else
            {
                throw ValidationException::withMessages([
                    'pin' => __('auth.failed')
                ]);
            }
        }
        throw ValidationException::withMessages([
            "input" => __('failed')
        ]);
    }

    public function verification(Request $request)
    {
        switch ($request->method) {
            case 'mobile':
                $request->validate([
                    'mobile' => 'required|mobile|exists:users'
                ]);
                break;

            default:
                $request->validate([
                    'username' => 'required|string||min:4|max:24'
                ]);
                break;
        }
        if($request->method == 'mobile')
        {
            $user = User::whereMobile($request->mobile)->first();
            if($user->status != 'awaiting')
            {
                throw ValidationException::withMessages([
                    $request->original_method => 'not.awaiting'
                ]);
            }
            $this->incrementVerificationAttempts($request);
            $verification = $user->createVerify();
            $this->statusMessage = 'success';
            $cache = $verification->bridge()->token;
            if(!Cache::get($cache))
            {
                Cache::put($cache, json_encode([
                    'user' => $user->serial,
                    'input' => 'pin'
                ]), 60 * 5);
            }
            return [
                'user' => new UserSummary($user, 'mobile'),
                'input' => 'pin',
                'url' => route('api.auth.verify', $cache),
                'expires_at' => $verification->bridge()->expires_at->timestamp
            ];
        }
    }

    public function forgetPassword(Request $request)
    {
        if ($request->method == 'mobile') {
            $this->incrementLoginAttempts($request);
            $request->validate([
                'mobile' => 'required|mobile|exists:users'
            ]);
            $user = User::whereMobile($request->mobile)->first();
            if ($user->status != 'active') {
                throw ValidationException::withMessages([
                    $request->original_method => 'auth.inactive'
                ]);
            }
            $this->incrementVerificationAttempts($request);
            $verification = $user->resetPassword();
            $this->statusMessage = 'success';
            $cache = $verification->bridge()->token;
            if (!Cache::get($cache)) {
                Cache::put($cache, json_encode([
                    'user' => $user->serial,
                    'input' => 'pin'
                ]), 60 * 5);
            }
            return [
                'user' => new UserSummary($user, 'mobile'),
                'input' => ['pin', 'password'],
                'url' => route('api.auth.resetPassword', $cache),
                'expires_at' => $verification->bridge()->expires_at->timestamp
            ];
        }
    }

    public function resetPassword(Request $request, $key)
    {
        $decrypted = Cache::getJson($key);
        $this->incrementLoginAttempts($request);
        $user = User::find(User::encode_id($decrypted->user));
        if ($user->status != 'active') {
            throw ValidationException::withMessages([
                $request->original_method => 'auth.inactive'
            ]);
        }
        $trust = $user->AuthVerify()->hasPin('reset_password', $user->mobile, $request->pin);
        if ($trust) {
            Cache::forget($key);
            $trust->verify();
            $user->password = $request->password;
            $user->update();
            $this->revokeAllToken($request);
            $this->statusMessage = 'success';
            return $this->show($request, $user);
        }
        else
        {
            throw ValidationException::withMessages([
                'pin' => __('auth.failed')
            ]);
        }
    }

    public function changePassword(Request $request)
    {
        if(!Hash::check($request->current_password, auth()->user()->password))
        {
            throw ValidationException::withMessages([
                "password" => __('auth.failed')
            ]);
        }
        $this->update($request, auth()->user());
        $this->revokeAllToken($request);
        $this->statusMessage = 'Password changed';
        return [];
    }

    public function revokeAllToken($request)
    {
        Token::where('user_id', auth()->id())->update(['revoked' => 1]);
    }

    public function enter(Request $request)
    {
        $once_method = false;
        $register = false;

        foreach ($request->all(['username', 'email', 'mobile']) as $key => $value) {
            if($value)
            {
                if($once_method)
                {
                    $register = true;
                    break;
                }
                $once_method = $key;
            }
        }

        $register_filed = $request->all();
        if(isset($register_filed['password']))
        {
            unset($register_filed['password']);
        }
        if (isset($register_filed[$once_method])) {
            unset($register_filed[$once_method]);
        }
        if(!empty($register_filed))
        {
            $register = true;
        }

        if(!$register && $once_method && User::where($once_method, $request->$once_method)->count() == 0)
        {
            $register = true;
        }

        $data = $request->all();
        $this->requestData($request, $register ? 'register' : 'login', $data);
        $request->validate($this->rules($request, $register ? 'register' : 'login'));
        $this->manipulateData($request, $register ? 'register' : 'login', $data);
        $request->replace($data);
        return $register ? $this->register($request) : $this->login($request);
    }

    public function loginUser(Request $request, User $user)
    {
        $user = $this->show($request, $user);
        if(!$user->idIs(auth()->id()))
        {
            $back_token_id = auth()->user()->token();
            $token = $this->createToken($request, $user, [], ['admin_id' => auth()->id()]);
            $back_token_id->revoke();
        }
        $user->additional(array_merge_recursive($user->additional, [
            'current' => $this->show($request, auth()->user()),
            'token' => $token
        ]));
        $this->statusMessage = 'succsess';
        return $user;
    }

    public function logout(Request $request)
    {
        $token = auth()->user()->token();
        $token->revoke();
        $this->statusMessage = 'logout';
        return [];
    }

    public function logoutLogin(Request $request)
    {
        $token = auth()->user()->token();
        if(isset($token->meta['admin_id']))
        {
            $admin = $this->model::findOrFail($token->meta['admin_id']);
            $this->logout($request);
            $user = $this->show($request, $admin);
            $token = $this->createToken($request, $user);
            $user->additional(array_merge_recursive($user->additional, [
                'token' => $token
            ]));
            $this->statusMessage = 'succsess';
            return $user;
        }
        return $this->logout($request);
    }

    protected function limiter()
    {
        return app(RateLimiter::class);
    }

    protected function incrementRegisterAttempts(Request $request)
    {
        $this->limiter()->hit(
            $this->throttlRegisterKey($request),
            30
        );
        if ($this->limiter()->tooManyAttempts($this->throttlRegisterKey($request), 5)) {
            throw new TooManyRequestsHttpException();
        }
    }
    protected function throttlRegisterKey(Request $request)
    {
        return Str::lower('register|' . $request->ip());
    }

    protected function incrementVerificationAttempts(Request $request)
    {
        $this->limiter()->hit(
            $this->throttlVerificationKey($request),
            60
        );
        if ($this->limiter()->tooManyAttempts($this->throttlVerificationKey($request), 2)) {
            throw new TooManyRequestsHttpException();
        }
    }
    protected function throttlVerificationKey(Request $request)
    {
        return Str::lower('verification|' . $request->ip());
    }

    protected function incrementLoginAttempts(Request $request)
    {
        $this->limiter()->hit(
            $this->throttlLoginKey($request),
            60
        );

        if ($this->limiter()->tooManyAttempts($this->throttlLoginKey($request), 10)) {
            throw new TooManyRequestsHttpException();
        }
    }
    protected function throttlLoginKey(Request $request)
    {
        return Str::lower($request->username . '|' . $request->ip());
    }
}
