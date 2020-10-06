<?php
/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/21/20, 5:58 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Http\Controllers\API\v1\Auth;
use iLaravel\Core\iApp\Http\Requests\iLaravel as Request;

trait Rules
{
    public function rules(Request $request, $action)
    {
        $rules = [];
        switch ($action) {
            case 'update':
                $rules = [
                    'avatar' => 'nullable|mimes:jpeg,jpg,png,gif|max:5120|dimensions:ratio=1',
                    'name' => 'nullable|string',
                    'family' => 'nullable|string',
                    'password' => 'nullable|min:6',
                    'website' => "nullable|max:191|regex:/^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z0-9][a-zA-Z0-9-_]+\.[a-zA-Z]{2,11}?$/",
                    'gender' => 'nullable|in:male,female',
                ];
                if (!$request->password) {
                    unset($rules['password']);
                }
                break;
            case 'store':
            case 'verify':
                $rules = $this->vendor->rules($request, $action);
                break;
        }
        return $rules;
    }
}
