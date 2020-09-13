<?php

namespace iLaravel\iAuth\iApp;

use iLaravel\Core\iApp\User;
use Illuminate\Database\Eloquent\Model;

class AuthTheory extends Model
{
    use \iLaravel\Core\iApp\Modals\Modal;
    use \iLaravel\Core\iApp\Modals\Metable;

    public static $s_prefix = 'IAT';
    public static $s_start = 1155;
    public static $s_end = 1733270554752;

    public $metaClass = AuthTheoryMeta::class;
    public $metaKeyName = 'theory_id';

    protected $guarded = [];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        parent::deleting(function (self $event) {
            self::resetRecordsId();
        });
    }

    public function getExpiredAtAttribute($value)
    {
        return format_datetime($value, $this->datetime, 'time');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
