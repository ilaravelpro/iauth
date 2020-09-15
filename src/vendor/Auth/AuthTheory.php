<?php

namespace App\Http\Controllers\API\Users;

use App\Requests\Maravel as Request;
use Illuminate\Validation\ValidationException;
use App\EnterTheory;
use App\EnterTheory\Fake;
use App\Http\Resources\User as ResourcesUser;
use App\User;

trait AuthTheory {

    public function theoryResult(Request $request, $response)
    {
        if (is_array($response) && isset($response[0]) && $response[0] instanceof User) {
            return $this->userAuthResult($request, ...$response);
        }
        return $response;
    }
    public function theory(Request $request, EnterTheory $enterTheory)
    {
        return $this->theoryResult($request, $enterTheory->theory->run($request)->response());
    }

    public function auth(Request $request)
    {
        $enterTheory = EnterTheory::where('key', $request->authorized_key)->first();
        if (!$enterTheory) {
            throw ValidationException::withMessages([
                "authorized_key" => __('auth.key')
            ]);
        }
        return $this->theoryResult($request, $enterTheory->theory->run($request)->response());
    }

    public function register(Request $request)
    {
        $theory = new Fake;
        return $this->theoryResult($request, $theory->create($request, 'register', $request->all(array_keys($this->rules($request, 'register'))))->response());
    }

    public function verification(Request $request)
    {
        $enterTheory = EnterTheory::where([
            'key' => $request->mobile,
            'theory' => 'register',
        ])->first();
        if($enterTheory)
        {
            return $this->theoryResult($request, $enterTheory->theory->run($request)->response());
        }
        abort(404);
    }

    public function recovery(Request $request)
    {
        $enterTheory = EnterTheory::where([
            'key' => $request->mobile,
            'theory' => 'auth',
        ])->first();
        if ($enterTheory) {
            return $this->theoryResult($request, $enterTheory->theory->create($request, 'recovery', $request->all(array_keys($this->rules($request, 'recovery'))))->response());
        }
        abort(404);
    }

    public function logout(Request $request)
    {
        auth()->user()->token()->revoke();
        return [];
    }

    public function authBack(Request $request)
    {
        auth()->user()->token()->revoke();
        if(isset(auth()->user()->token()->meta['admin_id']))
        {
            $user = User::find(auth()->user()->token()->meta['admin_id']);
            \Auth::setUser($user);
            $token = $user->createToken('api');
            $token->token->save();
            return $this->userAuthResult($request, $user, [
                'token' => $token->accessToken
            ]);
        }

        return [];
    }

    public function authAs(Request $request, User $user)
    {
        if ($user->type == 'admin')
        {
            throw ValidationException::withMessages([
                "user" => __('admin type is invalid')
            ]);
        }
        $current = auth()->user();
        auth()->user()->token()->revoke();
        \Auth::setUser($user);
        $token = $user->createToken('api');
        $token->token->meta = ['admin_id' => $current->id];
        $token->token->save();
        return $this->userAuthResult($request, $user, [
            'token' => $token->accessToken,
            'current' => new ResourcesUser($current)
        ]);
    }

    public function userAuthResult($request, $user, array $aditional = [])
    {
        $result = $this->me($request);
        $result->additional(array_merge_recursive($result->additional, $aditional));
        return $result;
    }
}
