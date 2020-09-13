<?php

function iauth_path($path = null)
{
    $path = trim($path, '/');
    return __DIR__ . ($path ? "/$path" : '');
}

function iauth($key = null, $default = null)
{
    return iconfig('iauth' . ($key ? ".$key" : ''), $default);
}
