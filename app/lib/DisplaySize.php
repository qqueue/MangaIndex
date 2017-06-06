<?php

namespace App\Lib;

class DisplaySize
{

    public static function format($size, $maxDp = 1)
    {
        if ($size === 0) {
            return '0';
        }

        $base = log($size) / log(1024);
        $suffixes = ['', 'K', 'M', 'G', 'T'];
        $suffix = $suffixes[floor($base)];

        $dp = $maxDp;
        if ($suffix === '' || $suffix === 'K') {
            $dp = 0;
        }
        
        return number_format(pow(1024, $base - floor($base)), $dp) . $suffix;
    }
}
