<?php

namespace App\Http\Controllers;

use App\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class ReportsController extends Controller
{

    public function reports()
    {
        Auth::basic('username');
        if (!Auth::check()) {
            // do auth
            Auth::basic('username');
            if (!Auth::check()) {
                return response(view('unauth', []), 401)->header('WWW-Authenticate', 'Basic');
            }
        }

        Report::clearCache();

        $reports = Report::select()
            ->with('pathRecord', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('reports', ['reports' => $reports, 'pageTitle' => 'Reports']);
    }

    public function dismiss()
    {
        // check we're logged in
        if (!Auth::check()) {
            Session::flash('redirect', URL::route('reports'));
            return Redirect::route('login');
        }

        //deprecated...
        $reportId = Request::get('report');

        ////replace with?
        //$reportId = $request->input('report')

        $report = Report::findOrFail($reportId);
        $report->delete();

        return Redirect::route('reports')->with('success', 'Report dismissed');
    }
}
