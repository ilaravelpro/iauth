<?php

namespace iLaravel\iAuth\iApp;

use Carbon\Carbon;
use iLaravel\Core\iApp\User;
use Illuminate\Database\Eloquent\Model;

class AuthTheory extends Model
{
    use \iLaravel\Core\iApp\Modals\Modal;

    protected $_theory, $_trigger;

    public static $s_prefix = 'IAT';
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
            if (!$event->token) $event->token = \Str::random(110);
        });
    }

    public function getExpiredAtAttribute($value)
    {
        return format_datetime($value, $this->datetime, 'time');
    }

    public function parent()
    {
        return $this->hasOne(static::class, 'id', 'parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTheoryAttribute()
    {
        if (!isset($this->attributes['theory'])) return null;
        if (!$this->_theory) $this->_theory = $this->findTheory($this->attributes['theory']);
        return $this->_theory;
    }

    public function getTriggerAttribute()
    {
        if (!isset($this->attributes['trigger'])) return null;
        if (!$this->_trigger) $this->_trigger = $this->findTheory($this->attributes['trigger']);
        return $this->_trigger;
    }

    public function findTheory($theory)
    {
        if (!($plan = config('auth.theories.' . $theory . '.model'))) throw new Exception("$theory Theory not found!");
        return new $plan($this);
    }

    public function resolveRouteBinding($value, $filed = null)
    {
        return $this->where('key', $value)->where('expired_at', '>', Carbon::now())->first();
    }

    public function toArray()
    {
        return [
            'key' => $this->attributes['key'],
            'theory' => $this->attributes['theory'],
        ];
    }

    public function bridges(){
        return $this->hasMany(imodal('AuthBridge'), 'theory_id');
    }
}
