<?php

namespace App\Http\Controllers;

use App\Lib\Path;
use App\PathRecord;
use Illuminate\Support\Facades\Auth;

class RecentController extends Controller
{

    public function recent()
    {
        Auth::basic('username');
        if (!Auth::check()) {
            // do auth
            Auth::basic('username');
            if (!Auth::check()) {
                return response(view('unauth', []), 401)->header('WWW-Authenticate', 'Basic');
            }
        }

        $records = $this->getRecentRecords();

        $paths = [];
        $bucket = [];
        $currentParent = null;
        foreach ($records as $record) {
            $path = Path::fromRelative($record->path);

            if ($path->exists()) {
                $path->record = $record;
                $parent = $path->getParent();

                if ($currentParent === null) {
                    $currentParent = $parent;
                }

                // if this path's parent is the same as the previous, add it to the bucket
                if ($parent->getHash() === $currentParent->getHash()) {
                    $bucket[] = $path;
                } else {
                    // if's different, add it to the paths array and start a new bucket
                    $paths[] = ['parent' => $currentParent, 'paths' => $bucket];
                    $bucket = [$path];
                    $currentParent = $parent;
                }
            }
        }

        if (count($bucket) > 0) {
            $paths[] = ['parent' => $currentParent, 'paths' => $bucket];
        }

        return view('recent', ['pathBuckets' => $paths, 'pageTitle' => 'Recent uploads']);
    }

    protected function getRecentRecords()
    {
        $records = PathRecord::whereDirectory(false)
            ->whereRaw('left(path, 5) in ("/Mang", "/Raws")') // TODO: Optimize this
            ->orderBy('modified', 'desc')
            ->take(250)
            ->get();

        return $records;
    }
}
