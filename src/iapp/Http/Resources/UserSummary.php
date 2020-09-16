<?php

namespace iLaravel\iAuth\iApp\Http\Resources;

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
        if ($this->_username_method !== 'id') $data[$this->_username_method] = $this->{$this->_username_method};
        //$data['avatar'] = $this->avatar ? new Files($this->avatar) : null;
        return $data;
    }
}
