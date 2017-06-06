<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Report extends Model
{

    public static function boot()
    {
        parent::boot();

        Report::saved(function ($report) {
            Report::clearCache();
        });

        Report::deleted(function ($report) {
            Report::clearCache();
        });
    }

    public static function clearCache()
    {
        Cache::forget('reportsCount');
    }

    public function pathRecord()
    {
        return $this->belongsTo(\App\PathRecord::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function getDisplayTime($short = false)
    {
        $time = strtotime($this->created_at);
        return DisplayTime::format($time, $short);
    }

    public function getPath()
    {
        return Path::fromRelative($this->path);
    }
}
