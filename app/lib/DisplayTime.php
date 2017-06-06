<?php

namespace App\Lib;

class DisplayTime
{

    public static function format($time, $short = false)
    {
        $now = new DateTime();
        $created = new DateTime(date('Y-m-d H:i:s', $time));

        $diff = $created->diff($now);
        
        if ($diff->d > 7 || $diff->m > 0) {
            return date('Y-m-d H:i', $time);
        } else {
            $steps = [
                'd' => [
                    'long' => ['1 day ago', '%d days ago'],
                    'short' => '%dd'
                ],
                'h' => [
                    'long' => ['1 hour ago', '%h hours ago'],
                    'short' => '%hh'
                ],
                'i' => [
                    'long' => ['1 minute ago', '%i minutes ago'],
                    'short' => '%im'
                ],
                's' => [
                    'long' => ['1 second ago', '%s seconds ago'],
                    'short' => '%ss'
                ]
            ];

            foreach ($steps as $var => $messages) {
                if ($diff->$var > 0) {
                    if ($short) {
                        return $diff->format($messages['short']);
                    }

                    if ($diff->$var === 1) {
                        return $messages['long'][0];
                    } else {
                        return $diff->format($messages['long'][1]);
                    }
                }
            }
        }
    }
}
