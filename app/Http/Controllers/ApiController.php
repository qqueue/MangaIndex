<?php

namespace App\Http\Controllers;

use App\PathRecord;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class ApiController extends Controller
{

    public function muid($muId)
    {
        $records = PathRecord::with('series.facets')->whereHas('series', function ($q) use ($muId) {
            $q->whereMuId($muId);
        })->get();

        $export = [];
        foreach ($records as $record) {
            if (strpos($record->path, '/Manga/') === 0) {
                // Skip paths
                if (strpos($record->path, '/Manga/Non-English') === 0) {
                    continue;
                } else {
                    $path = $record->getPath();
                    if ($path->exists()) {
                        $export[] = $record->toArray();
                    }
                }
            }
        }

        return Response::json(['result' => true, 'data' => $export]);
    }

    public function register()
    {
        $username = Request::get('username');
        $password = Request::get('password');

        if (!Auth::check()) {
            Auth::basic('username');
        }

        $user = Auth::user();
        if (!$user || !$user->hasSuper()) {
            return Response::json(['result' => false, 'message' => 'Access denied']);
        }

        if (!User::usernameIsUnique($username)) {
            return Response::json(['result' => false, 'message' => 'Username provided is already registered.']);
        }

        if ($username && $password) {
            User::register($username, $password);

            return Response::json(['result' => true]);
        } else {
            return Response::json(['result' => false, 'message' => 'Invalid details provided']);
        }
    }

    public function changePassword()
    {
        $username = Request::get('username');
        $old = Request::get('old');
        $new = Request::get('new');

        $user = User::getByUsernamePassword($username, $old);
        if (!$user) {
            return Response::json(['result' => false, 'message' => 'Username not found or password incorrect.']);
        } elseif ($new) {
            $user->setPassword($new);
            $user->save();

            return Response::json(['result' => true]);
        } else {
            return Response::json(['result' => false, 'message' => 'Invalid details provided']);
        }
    }
}
