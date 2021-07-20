<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 6:58 AM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp\Http\Resources;

use iLaravel\Core\iApp\Http\Resources\Resource;
use Illuminate\Http\Resources\Json\JsonResource;


class UserSummary extends JsonResource
{
    public $_username_method = false;

    public function __construct($user, $username = false)
    {
        parent::__construct($user);
        $this->_username_method = $username ?: 'username';

    }

    public function toArray($request)
    {
        $data = [];
        $data['id'] = $this->serial;
        $data['name'] = $this->name;
        if ($this->_username_method !== 'id' && $this->role != 'guest' && $this->{$this->_username_method}) {
            $method = $this->{$this->_username_method};
            if (in_array($this->_username_method, ['email', 'mobile']) && !is_string($method)) {
                $data[$this->_username_method] = new Resource($method);
                $data["{$this->_username_method}"] = $method->text;
            }else
                $data[$this->_username_method] = $method;
        }
        $data['session_method'] = $this->_username_method;
        //$data['avatar'] = $this->avatar ? new Files($this->avatar) : null;
        return $data;
    }
}
