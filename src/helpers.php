<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/13/20, 8:07 AM
 * Copyright (c) 2020. Powered by iamir.net
 */

function iauth_path($path = null)
{
    $path = trim($path, '/');
    return __DIR__ . ($path ? "/$path" : '');
}

function iauth($key = null, $default = null)
{
    return iconfig('iauth' . ($key ? ".$key" : ''), $default);
}
