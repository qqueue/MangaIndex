<?php

class IndexController extends BaseController {

    public function index($requestPath = '') {
        $path = Path::fromRelative('/'.$requestPath);

        if(!$path->exists()) {
            Auth::basic('username');
            if(!Auth::check()) {
                // do auth
                Auth::basic('username');
                if(!Auth::check()) {
                    return Response::make(View::make('unauth',array()),401)->header('WWW-Authenticate', 'Basic');
                }
            }
            App::abort(404, 'Path not found');
        }

        // if it's a file then download
        if($path->isFile()) {
            return $this->download($path);
        }

        $path->loadCreateRecord($path);
        $children = $this->exportChildren($path);

        $orderParams = $this->doSorting($children);

        $groupedStaff = null;
        $genres = null;
        $categories = null;
        $userIsWatching = null;
        $pageTitle = null;
        $pageDescription = null;
        $pageImage = null;
        $relatedSeries = null;
        
        if($series = $path->record->series) {
            $groupedStaff = $series->getGroupedStaff();
            $genres = $series->getFacetNames('genre');
            $categories = $series->getFacetNames('category');
            $pageTitle = $series->name;
            $pageDescription = $series->description;

            if($series->hasImage()) {
                $pageImage = $series->getImageUrl();
            }

            $relatedSeries = $series->getRelated();

            $user = Auth::user();
            if($user) {
                $userIsWatching = $user->isWatchingSeries($series);
            }
        }
        else {
            if(!$path->isRoot()) {
                $pageTitle = $path->getRelativeTop();
            }
        }
        
        $params = array(
            'path' => $path,
            'groupedStaff' => $groupedStaff,
            'genres' => $genres,
            'categories' => $categories,
            'breadcrumbs' => $path->getBreadcrumbs(),
            'children' => $children,
            'userIsWatching' => $userIsWatching,
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageImage' => $pageImage,
            'relatedSeries' => $relatedSeries
        );

        $params = array_merge($params, $orderParams);
        $updated = 0;
        foreach($children as $child) {
            if (!$child->isDir && $child->rawTime > $updated) {
                $updated = $child->rawTime;
            }
        }
        $params['updated'] = $updated;
        if (Request::format() == 'atom' || Input::get('t') == 'atom') {
            return Response::make(View::make('index-atom', $params))->header(
              'Content-Type', 'application/atom+xml; charset=UTF-8')->setMaxAge(3600)->setPublic();
        } else if (Request::format() == 'rss' || Input::get('t') == 'rss') {
            return Response::make(View::make('index-rss', $params))->header(
                'Content-Type', 'application/rss+xml; charset=UTF-8')->setMaxAge(3600)->setPublic();
        } else {
            Auth::basic('username');
            if(!Auth::check()) {
                // do auth
                Auth::basic('username');
                if(!Auth::check()) {
                    return Response::make(View::make('unauth',array()),401)->header('WWW-Authenticate', 'Basic');
                }
            }
            return View::make('index', $params);
        }
    }

    protected function doSorting(&$children) {
        $orderMethod = Input::get('order', 'name');
        $orderDir = Input::get('dir', 'asc');

        if(!Sorting::validOrderMethod($orderMethod)) {
            $orderMethod = 'name';
        }

        if(!Sorting::validOrderDirection($orderDir)) {
            $orderDir = 'asc';
        }
        
        // if the values are default then skip sorting as the paths already in order
        if($orderMethod !== 'name' || $orderDir !== 'asc') {

            Sorting::sort($children, $orderMethod, $orderDir);
        }

        $invOrderDir = ($orderDir === 'asc') ? 'desc' : 'asc';

        $params = array(
            'orderMethod' => $orderMethod,
            'orderDir' => $orderDir,
            'invOrderDir' => $invOrderDir
        );

        return $params;
    }

    protected function exportChildren(Path $path) {
        $children = array();
        $pathChildren = $path->getChildren();

        foreach($pathChildren as $child) {
            $hash = $child->getHash();

            $children[] = Cache::tags('paths')->rememberForever($hash, function() use($child) {
                return $child->export();
            });
        }

        return $children;
    }

    public function save() {
        $recordId = Input::get('record');
        $muId = Input::get('mu_id');
        $locked = Input::get('locked');
        $delete = Input::get('delete');
        $update = Input::get('update');
        $comment = Input::get('comment');

        // load record
        $record = PathRecord::findOrFail($recordId);

        // remove series link
        if($delete) {
            $record->series_id = null;
        }
        else {
            if($update) { // download new data from MU
                if($record->series) {
                    $record->series->updateMuData();
                }
            }
            elseif($muId) {
                // get series
                $series = Series::getCreateFromMuId(base_convert($muId, 36, 10));
                if(!$series) {
                    Session::flash('error', 'Failed to find series for MU ID');
                    return Redirect::back();
                }

                $record->series_id = $series->id;
            }

            $record->comment = $comment;

            $user = Auth::user();
            if($user && $user->hasSuper()) {
                $record->locked = !!$locked;
            }
        }

        $record->save();

        Session::flash('success', 'Saved path details successfully');
        return Redirect::back();
    }

    public function report() {
        $recordId = Input::get('record');
        $reason = Input::get('reason');

        if(!$reason) {
            Session::flash('error', 'Please enter a report reason!');
            return Redirect::back();
        }

        $count = Report::where('path_record_id', '=', $recordId)->count();
        if($count > 0) {
            Session::flash('error', 'This path has already been reported');
            return Redirect::back();
        }

        // load record
        $record = PathRecord::findOrFail($recordId);

        if($record->locked) {
            Session::flash('error', 'You cannot report this directory!');
            return Redirect::back();
        }

        $report = new Report();
        $report->path_record_id = $record->id;
        $report->path = $record->path;
        $report->reason = $reason;

        $user = Auth::user();
        if($user) {
            $report->user_id = $user->id;
        }

        $report->save();

        Session::flash('success', 'Report submitted');
        return Redirect::back();
    }
}
