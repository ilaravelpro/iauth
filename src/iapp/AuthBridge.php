<?php

namespace iLaravel\iAuth\iApp;

use Carbon\Carbon;
use iLaravel\Core\iApp\User;
use Illuminate\Database\Eloquent\Model;

class AuthBridge extends Model
{
    use \iLaravel\Core\iApp\Modals\Modal;

    public static $s_prefix = 'IAB';
    public static $s_start = 1155;
    public static $s_end = 1733270554752;

    protected $guarded = [];

    protected $casts = [
        'verified_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        parent::deleting(function (self $event) {
            self::resetRecordsId();
        });
        parent::creating(function (self $event) {
            if (!$event->token) $event->token = \Str::random(69);
            if (!$event->pin) $event->pin = rand(100000, 999999);
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function findByToken($token)
    {
        return static::where([
            'token' => $token,
            ['expires_at', '>', Carbon::createFromTimestamp(time())]
        ])
            ->whereNull('verified_at')
            ->first();
    }

    public static function findByTypeBridgePin($type, $bridge, $pin)
    {
        return static::where([
            'type' => $type,
            'bridge' => $bridge,
            'pin' => $pin,
            ['expires_at', '>', Carbon::createFromTimestamp(time())]
        ])
            ->whereNull('verified_at')
            ->first();
    }

    public function verify()
    {
        if ($this->type == 'reset_password') {
            $this->delete();
            return;
        }
        $now = Carbon::now();
        if ($this->type == 'mobile' && $this->user->status == 'awaiting') {
            $this->user->mobile = $this->bridge;
            $this->user->status = 'active';
            $this->user->update();
        }
        if ($this->type == 'email' && $this->user->status == 'awaiting') {
            $this->user->status = 'active';
            $this->user->email = $this->bridge;
            $this->user->email_verified_at = $now;
            $this->user->update();
        }
        $this->expires_at = null;
        $this->token = null;
        $this->pin = null;
        $this->verified_at = $now;
        $this->save();
    }
}
