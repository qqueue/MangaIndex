<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

class Controller extends BaseController
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        //
    }

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if (! is_null($this->layout)) {
            $this->layout = view($this->layout);
        }
    }

    protected function download(Path $path)
    {
        // check basic auth headers as well, so if specified,
        // client doesn't have to go through the login and cookie dance.
        Auth::onceBasic("username");
        // check we're logged in
        if (!Auth::check()) {
            Session::flash('redirect', URL::current());
            return Redirect::route('login');
        }

        // record the download in the db
        $record = $path->loadCreateRecord($path);
        $record->downloaded_at = $record->freshTimestamp();
        $record->increment('downloads');
        $record->save();

        $isMisc = (strpos($path->getRelative(), '/Misc/') === 0);

        if ($isMisc || $path->isSafeExtension()) { // check if the extension is safe to download
            $file = new AsciiSafeDownloadFile($path->getPathname()); // see comments in AsciiSafeDownloadFile class

            $baseName = $path->getBasename();
            $baseName = str_replace('%', '', $baseName);

            try {
                return Response::download($file, $baseName);
            } catch (InvalidArgumentException $e) {
                abort(500, 'This file has a malformed filename. Please contact an admin.');
            }
        } else {
            abort(403, sprintf('File type "%s" not allowed', $path->getExtension()));
        }
    }
}
