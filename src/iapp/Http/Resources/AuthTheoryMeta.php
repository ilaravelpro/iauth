<?php

namespace iLaravel\iAuth\iApp\Http\Resources;

use iLaravel\Core\iApp\Http\Resources\Resource;

class AuthTheoryMeta extends Resource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);
        unset($data['id']);
        unset($data['id_text']);
        unset($data['key']);
        unset($data['point_id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        return $data;
    }
}
