<?php

namespace iLaravel\iAuth\iApp;

use iLaravel\Core\iApp\Modals\MetaData;
use Illuminate\Support\Facades\DB;

class AuthTheoryMeta extends MetaData
{
    use \iLaravel\Core\iApp\Modals\Modal;

    public static $s_prefix = 'IWPM';
    public static $s_start = 1155;
    public static $s_end = 1733270554752;

    protected $table = 'auth_theories_meta';

    protected $guarded = [];

}
