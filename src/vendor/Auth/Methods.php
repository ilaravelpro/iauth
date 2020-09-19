<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 12:35 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace App\Http\Controllers\API\Users;

use App\EnterTheory;
use App\File;
use App\Requests\Maravel as Request;
use App\Guardio;
use App\Http\Resources\User as ResourcesUser;
use App\User;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

trait Methods {
    public function index(Request $request)
    {
        return $this->_index(...func_get_args());
    }

    public function queryIndex($request)
    {
        $model = $this->model::select('users.*');
        if(!Guardio::has('view-inactive-user'))
        {
            $model->where('status', 'active');
        }
        if (!$request->type && !Guardio::has('users.viewAny.all')) {
            $types = [];
            foreach (Guardio::permissions() as $key => $value) {
                if (substr($key, 0, 14) == 'users.viewAny.') {
                    $types[] = substr($key, 14);
                }
            }
            $model->whereIn('type', $types);
        }
        return [null, $model];
    }

    public function show(Request $request, User $user)
    {
        return $this->_show(...func_get_args());
    }

    public function store(Request $request)
    {
        return $this->_store($request, function($request, $data){
            DB::beginTransaction();
            $user = $this->model::create($data);
            foreach (['username', 'email', 'mobile'] as $value) {
                if($user->$value){
                    EnterTheory::create([
                        'key' => $user->$value,
                        'theory' => 'auth',
                        'trigger' => 'password',
                        'user_id' => $user->id
                    ]);
                }
            }
            DB::commit();
            return $user;
        });
    }

    public function update(Request $request, User $user)
    {
        return $this->_update($request, $user, function($request, $user, $data){
            DB::beginTransaction();
            $user->update($data);
            foreach (['username', 'email', 'mobile'] as $value) {
                if ($user->$value != $user->getOriginal($value)) {
                    if($find = EnterTheory::where('key', $user->getOriginal($value))->where('theory', 'auth')->first())
                    {
                        $find->update(['key' => $user->$value]);
                    }
                    else
                    {
                        EnterTheory::create([
                            'key' => $user->$value,
                            'theory' => 'auth',
                            'trigger' => $user->status == 'active' ? 'password' : 'mobileCode',
                            'user_id' => $user->id
                        ]);
                    }
                }
            }
            DB::commit();
        });
    }

    public function changePassword(Request $request, User $user){
        if(!auth()->isAdmin() && !Hash::check($request->password, $user->password)){
            throw ValidationException::withMessages([
                "password" => __('validation.password-match')
            ]);
        }
        if (Hash::check($request->new_password, $user->password)) {
            throw ValidationException::withMessages([
                "password" => __('validation.password-old')
            ]);
        }
        $user->update(['password' => Hash::make($request->new_password)]);
        $this->statusMessage = __('Password changed');
        return [];
    }

    public function avatar(Request $request, User $user)
    {
        return File::attachment($request->avatar, function($attachment) use($user) {
            \DB::beginTransaction();
            $avatar = $attachment->createPost([
                'type' => 'attachment:avatar',
                'status' => 'publish'
            ]);
            $user->avatar_id = $avatar->id;
            $user->save();
            $file = $attachment->createFile();
            $file->changeSize(500, null, 'large');
            $file->changeSize(250, null, 'medium');
            $file->changeSize(150, null, 'small');
            $user->avatar;
            \DB::commit();
            return new ResourcesUser($user);
        });
    }

    public function me(Request $request)
    {
        $show = $this->show($request, auth()->user());
        $token = auth()->user()->token();
        $show->additional(array_merge_recursive($show->additional, [
            'guards' => Guardio::permissions()
        ]));
        if (isset($token->meta['admin_id'])) {
            $admin = $this->model::findOrFail($token->meta['admin_id']);
            $user = $this->show($request, $admin);
            $show->additional(array_merge_recursive($show->additional, [
                'current' => $user
            ]));
        }
        return $show;
    }

    public function meUpdate(Request $request)
    {
        return $this->update($request, auth()->user());
    }

}
