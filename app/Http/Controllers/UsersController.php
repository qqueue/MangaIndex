<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Notification;
use App\Series;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

class UsersController extends Controller
{

    public function notifications()
    {
        $user = Auth::user();

        $notifications = $user
            ->notifications()
            ->join('path_records', 'path_records.id', '=', 'notifications.path_record_id')
            ->select('notifications.*')
            ->with('pathRecord')
            ->orderBy('notifications.dismissed', 'asc')
            ->orderBy('path_records.modified', 'desc')
            ->paginate(20);

        $watched = $user->series()->with('pathRecords')->get();

        $params = [
            'notifications' => $notifications,
            'watched' => $watched,
            'pageTitle' => 'Notifications'
        ];

        return view('notifications', $params);
    }

    public function opml()
    {
        // check we're not already logged in
        if (!Auth::check()) {
            // do auth
            Auth::basic('username');
        }

        $user = Auth::user();

        $watched = $user->series()->with('pathRecords')->get();

        $params = [
            'watched' => $watched
        ];

        return response(view('opml', $params))->header(
            'Content-Type',
            'text/xml; charset=UTF-8'
        )->header(
            'Content-Disposition',
            'attachment;filename=madokami-watched.opml'
        );
    }

    public function dismiss()
    {
        $user = Auth::user();

        if (Request::has('all')) { // dismiss all
            $user->notifications()->update(['dismissed' => true]);
            Session::flash('success', 'All notifications dismissed');
        } else { // dismiss single

            //deprecated...
            $notifyId = Request::get('notification');
            ////replace with?
            //$notifyId = $request->input('notification')

            $notify = Notification::findOrFail($notifyId);

            if ($notify->user_id !== $user->id) {
                abort(403, 'That notification doesn\'t belong to you');
            }

            $notify->dismiss();
        }

        return Redirect::route('notifications');
    }

    public function toggleWatch()
    {
        //deprecated...
        $seriesId = Request::get('series');
        ////replace with?
        //$seriesId = $request->input('series')

        $series = Series::findOrFail($seriesId);
        $user = Auth::user();

        if (!$series) {
            abort(400, 'Invalid params');
        } else {
            $watching = $user->watchSeries($series);

            if (Request::ajax()) {
                return Response::json(['result' => true, 'watching' => $watching]);
            } else {
                return Redirect::back();
            }
        }
    }

    public function downloadDismiss(Notification $notification)
    {
        $user = Auth::user();
        if ($notification->user_id !== $user->id) {
            abort(403, 'That notification doesn\'t belong to you');
        }

        $notification->dismiss();

        $path = $notification->pathRecord->getPath();
        return $this->download($path);
    }

    public function authcheck()
    {
        Auth::onceBasic('username');
        if (Auth::check()) {
            return response('', 204);
        } else {
            return response('', 401, ['WWW-Authenticate' => 'Basic']);
        }
    }

    public function login()
    {
        $redirect = Session::get('redirect');

        // check we're not already logged in
        if (!Auth::check()) {
            // do auth
            Auth::basic('username');

            //check again
            if (Auth::check()) {
                // auth successful
                $user = Auth::user();
                $user->touchLoggedInDate(); // update logged_in_at to current datetime
                Auth::login($user, true); // login and set remember_token
            } else {
                // auth failed
                $headers = [
                    'WWW-Authenticate' => 'Basic'
                ];

                $params = [
                    'title' => 'Login failed',
                    'message' => 'Invalid username/password.'
                ];

                Session::flash('redirect', $redirect);
                return Response::view('message', $params, 401, $headers);
            }
        }

        if ($redirect) {
            return redirect($redirect);
        } else {
            return Redirect::home();
        }
    }

    public function logout()
    {
        Auth::logout();
        return Redirect::home();
    }
}
