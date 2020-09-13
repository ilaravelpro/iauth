<?php

namespace iLaravel\iAuth\iApp;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use iLaravel\iAuth\Vendor\WindyPoint as Vendor;

class AuthTheory extends Model
{
    use \iLaravel\Core\iApp\Modals\Modal;

    public static $s_prefix = 'IAT';
    public static $s_start = 1155;
    public static $s_end = 1733270554752;

    protected $guarded = [];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        parent::deleting(function (self $event) {
            $event->meta()->delete();
            self::resetRecordsId();
        });
    }

    public function meta()
    {
        return $this->hasMany(WindyPointMeta::class, 'point_id');
    }

    public function getExpiredAtAttribute($value)
    {
        return format_datetime($value, $this->datetime, 'time');
    }

    public static function getByLonLat(float $lon, float $lat)
    {
        return static::where('latitude', round($lon, 4))
            ->where('longitude', round($lat, 4))->orderBy('valid_at')->get();
    }

    public static function findByLonLat(float $lon, float $lat, $valid_at = null, $name = null)
    {
        if (static::where('latitude', round($lat, 4))
                ->where('longitude', round($lon, 4))
                ->where('valid_at', '>=', $valid_at ?: Carbon::now()->addDay()->format('Y-m-d H:i:s'))
                ->orderBy('valid_at')->count() < 5)
            Vendor::handelImport(['lon' => $lon, 'lat' => $lat]);
        return static::where('latitude', round($lat, 4))
            ->where('longitude', round($lon, 4))
            ->where('valid_at', '>=', $valid_at ?: Carbon::now()->format('Y-m-d H:i:s'))
            ->orderBy('valid_at')
            ->first();
    }

    public static function whereFirst(float $lon, float $lat, $model = 'gfs')
    {
        $model = static::where('latitude', round($lat, 4))
            ->where('longitude', round($lon, 4));
        if ($model) $model->where('model', $model);
        return $model;
    }
}
