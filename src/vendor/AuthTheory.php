<?php

namespace iLaravel\iAuth\Vendor;

use Illuminate\Support\Facades\Cache;

class AuthTheory
{
    public $service_url = "https://api.windy.com/api/point-forecast/v2";

    public $params = [
        "lat" => 33.560000,
        "lon" => 53.447500,
        "model" => "gfs",
        "parameters" => ["wind", "temp"],
        "levels" => ["surface", "1000h", "950h", "925h", "900h", "850h", "800h", "700h", "600h", "500h", "400h", "300h", "200h", "150h"],
    ];

    public $units = ["m*s-1" => "m/s", "J*kg-1" => "J/kg", "µg*m-3" => "µg/m³"];

    public $data = [];

    const WINDY_POINT_CACHE_KEY = "iauth_POINT_CACHE_";
    protected $cache_time = 300000;

    public function __construct($params = [])
    {
        $this->params = array_merge($this->params, $params, request()->all());
    }

    public static function handel($params = []) {
        $self = new self($params);
        $data = $self->get();
        $cdata = [];
        if (isset($data['ts']))
            foreach ($data['ts'] as $index => $time) {
                foreach ($data['units'] as $undex => $unit) {
                    $exp = explode('-',$undex);
                    if (substr($exp[1], -1, 1) == 'h')
                        $exp[1] = substr($exp[1] , 0, -1);
                    if ($exp[1] == 'surface')
                        $exp[1] = 0;
                    if (array_search($unit, array_keys($self->units)) !== false)
                        $unit = array_values($self->units)[array_search($unit, array_keys($self->units))];
                    $cdata["$time"][$exp[0]][$exp[1]] = [$data[$undex][$index], $unit];
                }
            }
        return $cdata;
    }

    public static function handelImport($params = []) {
        $self = new self($params);
        $data = $self->get();
        $cdata = [];
        if (isset($data['ts']))
            foreach ($data['ts'] as $index => $time) {
            if (!\iLaravel\iAuth\iApp\WindyPoint::where('latitude', round($self->params['lat'], 4))
            ->where('longitude', round($self->params['lon'], 4))
            ->where('valid_at', date('Y-m-d H:i:s', ($time / 1000)))->first()){
                $point = \iLaravel\iAuth\iApp\WindyPoint::create([
                    "latitude" => round($self->params['lat'], 4),
                    "longitude" => round($self->params['lon'], 4),
                    "valid_at" => date('Y-m-d H:i:s', ($time / 1000)),
                ]);
                foreach ($data['units'] as $undex => $unit) {
                    $exp = explode('-',$undex);
                    if (substr($exp[1], -1, 1) == 'h')
                        $exp[1] = substr($exp[1] , 0, -1);
                    if ($exp[1] == 'surface')
                        $exp[1] = 0;
                    if (array_search($unit, array_keys($self->units)) !== false)
                        $unit = array_values($self->units)[array_search($unit, array_keys($self->units))];
                    $point->meta()->create([
                        'key' => $exp[0],
                        'level' => $exp[1],
                        'value' => $data[$undex][$index],
                        'unit' => strtolower($unit),
                    ]);
                }
            }
        }
        return $cdata;
    }

    public function get()
    {
        if ($this->data) return $this->data;
        /*if (Cache::has($this->getCacheKey()))
            return Cache::get($this->getCacheKey());*/
        $data = $this->request();
        if (!isset($data) || !$data) return [];
        $this->data = $data;
        //Cache::put($this->getCacheKey(), $this->data, $this->cache_time);
        return (array) $this->data;
    }


    public function request() {
        $params = $this->params;
        $params['key'] = config('services.windy.key');
        $headers = array(
            'Content-Type: application/json',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->service_url);
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $params ) );
        return json_decode(curl_exec($ch));
    }

    public function getCacheKey() {
        $params =  $this->params;
        return self::WINDY_POINT_CACHE_KEY . md5(http_build_query($params));
    }
}
