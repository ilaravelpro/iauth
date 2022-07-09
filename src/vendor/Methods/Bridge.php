<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 5:54 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\Vendor\Methods;

class Bridge
{
    public static function sort($model, $session, $theory, $method)
    {
        $bridges = $activists = static::get($model, $session, $theory, true);
        if (in_array(iauth('methods.verify.mode'), $activists))
            $bridges = [iauth('methods.verify.mode')];
        elseif (iauth('methods.verify.mode') !== 'all' && $model->{iauth('methods.verify.other')}) {
            $bridges = [in_array($method, $activists) ? $method : iauth('methods.verify.other')];
            $bridges = count($bridges) ? $bridges : $activists;
        }
        return $bridges;
    }

    public static function get($model, $session, $theory, $key = false)
    {
        $bridges = array_filter(iauth('bridges.models'), function ($bridge, $key) use ($model, $theory, $session) {
            return $bridge['status'] && in_array($theory, $bridge['sessions']) && ($model && $model->role !== 'guest' && $theory != $key ? $model->{$key} : true);
        }, 1);
        return $key ? is_string($key) ? array_column($bridges, $key) : array_keys($bridges) : $bridges;
    }
}
