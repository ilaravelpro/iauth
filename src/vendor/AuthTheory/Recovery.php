<?php

namespace iLaravel\iAuth\Vendor\AuthTheory;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Recovery extends Theory
{
    public function boot(Request $request)
    {
        return $this->pass($request);
    }

    public function passed(Request $request)
    {
        $user = $this->userModel::find($this->model->user_id);
        $user->update(['password' => Hash::make($request->password)]);
        foreach($user->tokens as $token)
            $token->revoke();
        $this->model->delete();
        return $this->model->parent->theory->run($request);
    }

    public function register(Request $request, $model = null, array $parameters = [])
    {
        $theory = $this->theoryModel::where('parent_id', $model->id)
            ->where('theory', 'recovery')
            ->where('expired_at', '>', Carbon::now())
            ->first();
        if ($theory) {
            throw ValidationException::withMessages([
                $request->original_method => __('Try after :seconds seconds', ['seconds' => Carbon::now()->diffInSeconds($theory->expired_at)])
            ]);
        }
        $theory = $this->theoryModel::create([
            'parent_id' => $model->id,
            'user_id' => $model->user_id,
            'key' => $this->theoryModel::tokenGenerator(),
            'theory' => 'recovery',
            'trigger' => 'mobileCode',
            'expired_at' => Carbon::now()->addMinutes(3)
        ]);
        if ($theory->getAttribute('trigger')) {
            return $theory->trigger->register($request, $theory);
        }
        return $theory;
    }

    public function rules(Request $request)
    {
        return [
            'password' => 'required|min:6'
        ];
    }
}
