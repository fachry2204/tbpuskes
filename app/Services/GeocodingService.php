<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
class GeocodingService { public function reverse(float $latitude,float $longitude): ?string { $url=config('services.geocoding.nominatim_url'); if(!$url)return null; try{return Http::acceptJson()->withUserAgent('Monitoring-TB/1.0')->timeout(8)->get($url,['lat'=>$latitude,'lon'=>$longitude,'format'=>'jsonv2'])->json('display_name');}catch(\Throwable){return null;} } }
