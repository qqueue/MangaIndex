<?php

namespace Archive;

class Utils
{

    public static function filterImageFiles($files)
    {
        $result = [];

        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $ext = strtolower($ext);

            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $result[] = $file;
            }
        }

        return $result;
    }
}
