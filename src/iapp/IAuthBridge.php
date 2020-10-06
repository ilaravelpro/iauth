<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 9:27 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

namespace iLaravel\iAuth\iApp;

use Carbon\Carbon;
use iLaravel\Core\iApp\User;
use Illuminate\Database\Eloquent\Model;

class IAuthBridge extends Model
{
    use \iLaravel\Core\iApp\Modals\Modal;

    public static $s_prefix = 'IAB';
    public static $s_start = 1155;
    public static $s_end = 1733270554752;

    protected $guarded = [];

    protected $casts = [
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        parent::deleting(function (self $event) {
            self::resetRecordsId();
        });
        parent::creating(function (self $event) {
            if (!$event->pin) $event->pin = rand(100000, 999999);
            if (!$event->expires_at) $event->expires_at =  Carbon::now()->addMinutes(iauth('bridges.expired.time'));
        });
    }

    public function getVerifiedAtAttribute($value)
    {
        return format_datetime($value, $this->datetime, 'time');
    }

    public function getExpiredAtAttribute($value)
    {
        return format_datetime($value, $this->datetime, 'time');
    }

    public function session()
    {
        return $this->belongsTo(imodal('IAuthSession'), 'session_id');
    }
}
