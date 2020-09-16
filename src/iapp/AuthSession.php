<?php

namespace iLaravel\iAuth\iApp;

use Carbon\Carbon;
use iLaravel\Core\iApp\User;
use Illuminate\Database\Eloquent\Model;

class AuthSession extends Model
{
    use \iLaravel\Core\iApp\Modals\Modal;

    protected $_theory, $_trigger;

    public static $s_prefix = 'IAS';
    public static $s_start = 1155;
    public static $s_end = 1733270554752;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
        'expired_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        parent::deleting(function (self $event) {
            self::resetRecordsId();
        });
        parent::creating(function (self $event) {
            if (!$event->token) $event->token = self::generateToken($event->session);
            if (!isset($event->attributes->expired_at)) $event->attributes['expired_at'] = Carbon::now()->addMinutes(iauth('sessions.expired.time'));
        });
    }

    public static function generateToken($session, $token = null) {
        if (!$token || static::findByToken($session, $token))
            return static::generateToken($session, \Str::random(110));
        return $token;
    }

    public function getExpiredAtAttribute($value)
    {
        return format_datetime($value, $this->datetime, 'time');
    }

    public static function getByModelId($model, $id)
    {
        return static::where('model', $model)->where('model', $id)->get();
    }

    public static function findByToken($session, $token)
    {
        return static::where('session', $session)->where('token', $token)->where('verified', 0)->where('revoked', 0)->where('expired_at', '>', Carbon::now())->first();
    }

    public function bridges(){
        return $this->hasMany(imodal('AuthBridge'), 'session_id');
    }

    public function bridgesByMobile(){
        return $this->hasMany(imodal('AuthBridge'), 'session_id')->where('method' , 'mobile');
    }

    public function bridgesByEmail(){
        return $this->hasMany(imodal('AuthBridge'), 'session_id')->where('method' , 'email');
    }

    public function item() {
        if ($this->model){
            $model = imodal($this->model);
            return $model::find($this->model_id);
        }
        return null;
    }
}
