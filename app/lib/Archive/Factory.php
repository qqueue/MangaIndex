<?php

namespace Archive;

class Factory
{

    public static function open(\Path $path)
    {
        $ext = $path->getExtension();

        switch ($ext) {
            case 'zip':
            case 'cbz':
                return new ZipArchive($path);
            case 'rar':
            case 'cbr':
                return new RarArchive($path);
            default:
                throw new \Exception('Invalid file type: '.$ext);
        }
    }
}
