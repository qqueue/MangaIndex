<?php

namespace App\Lib;

use Illuminate\Support\Facades\Log;

class Indexer
{

    public static function index(Path $path, $levels = null, &$count = 0)
    {
        printf("Indexing: %s\n", $path->getRelative());
        if (preg_match("/^\.in\./", $path->getFilename())) {
            return;
        }

        try {
            $record = $path->loadRecord();

            // if this path does not exist, and we have a record then delete it
            if (!$path->exists()) {
                if ($record) {
                    $record->delete();
                }

                return;
            }

            if (!$record) {
                // none exists, create it
                $path->loadCreateRecord();
                $count++;
            } else {
                // it exists, check if it needs updating
                if ($record->checkUpdate($path)) {
                    $count++;
                }
            }

            // index children
            if ($levels > 0 || $levels === null) {
                if ($levels !== null) {
                    $levels--;
                }

                if ($path->isDir()) {
                    $children = $path->getChildren();

                    foreach ($children as $child) {
                        self::index($child, $levels, $count);
                    }
                }
            }
        } catch (Exception $e) {
            print((string)$e);
            Log::error($e, ['path' => $path->getRelative()]);
        }
    }
}
