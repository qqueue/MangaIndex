<?php

namespace App\Http\Controllers;

use App\Lib\Search;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class SearchController extends Controller
{

    public function search($keyword = null)
    {
        Auth::basic('username');
        if (!Auth::check()) {
            // do auth
            Auth::basic('username');
            if (!Auth::check()) {
                return response(view('unauth', []), 401)->header('WWW-Authenticate', 'Basic');
            }
        }
        if ($keyword) {
            $match = $keyword;
        } else {
            $match = Request::get('q');
        }

        $count = 0;
        if ($match) {
            $result = Search::searchPaths($match, $count);
        } else {
            $result = [];
        }

        $paths = [];
        foreach ($result as $row) {
            $path = $row->getPath();

            if ($path->exists()) {
                $path->record = $row;
                $paths[] = $path;
            }
        }

        // page title
        $pageTitle = 'Search: '.$match;

        return view('search', ['keyword' => $match, 'paths' => $paths, 'pageTitle' => $pageTitle, 'count' => $count]);
    }

    // route for e.g /search/genre/drama
    public function searchKeywordType($type = null, $keyword = null)
    {
        Auth::basic('username');
        if (!Auth::check()) {
            // do auth
            Auth::basic('username');
            if (!Auth::check()) {
                return response(view('unauth', []), 401)->header('WWW-Authenticate', 'Basic');
            }
        }
        if ($type && $keyword) {
            $match = sprintf('"%s:%s"', $type, $keyword);
        } else {
            $match = '';
        }

        return $this->search($match);
    }
    
    public function suggest()
    {
        Auth::basic('username');
        if (!Auth::check()) {
            // do auth
            Auth::basic('username');
            if (!Auth::check()) {
                return response(view('unauth', []), 401)->header('WWW-Authenticate', 'Basic');
            }
        }
        $term = Request::get('term');
        $result = Search::suggest($term);
        return Response::json($result);
    }
}
