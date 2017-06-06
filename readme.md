### TODO

* Implement proper error catching in WatcherCommand - this must never exit
* ~~Fix issue with view variables not being declared in views presented by App::error()~~
* Pagination on search
* ~~Store path name rather than path id in reports to preserve report records when paths get deleted~~
* Create "reverse index" command
* Create command to find malformed filenames


### TODO POST 5.4 UPGRADE

* Whitespace is handled in request data now. Check data in app isn't affected.
* Audit and clean .js files.
* Double check configs.
* Testing has changed. It's now via the new BrowserKit Testing package. Check tests.
* Date cast now creates a Carbon object which calls startOfDay. Check for anywhere that the time portion of date is being used and use the datetime cast.
* 5.4 no longer includes ability to customise the PDO fetch style. PDO::FETCH_OBJ is always used now. Check the usage in database and work around if no longer able to use even deprecated.
* Unsure on current MySQL version being used. If it's pre 5.7.7 (guessing so?) it spit out error 'specified key was too long'. Upgrade MySQL or change some code. There is a post with info on the laravel blog.
* Possible issues with IndexController and UsersController due to "Illuminate\Support\Facades\Request" and "App\Http\Requests\Request".
* Check and confirm 5.3 split to LoginController and RegisterController is OK. Same for ResetPasswordController.
* Check AuthController routes because of above.
* Check RedirectIfAuthenticated for correct dir for server, not sure what it is for production. We don't want infinite redirects.
* Review routes in general because of 5.3 changes (splits).
* where no longer performs strict comparisons. May need to use whereStrict in places.
  * app/Lib/Search.php
  * app/Console/Commands/MergeAutoUploadsCommand.php
  * app/Http/Controllers/IndexController.php
  * app/Notification.php
  * app/Series.php
  * app/Observers/PathRecordNotifications.php
  * routes/web.php
* If starting a scope with orWhere it will no longer be converted to a normal where as of 5.3. Check for issues.
* JoinClause class was rewritten in 5.3, optional param $where of clause on and the bindings property were removed.
  * app/Console/Commands/MergeAutoUploadsCommand.php
  * app/Http/Controllers/UsersController.php
  * app/Series.php
* Query Builder now returns collections instead of plain arrays. Need to check code and upgrade to use collections, or chain the all() method onto queries to return a plain array.
  * app/Composers/GlobalComposer.php
  * app/Console/Commands/UpdateSizeCommand.php
  * app/Console/Commands/MergeAutoUploadsCommand.php
  * app/Series.php
* slice, chunk and reverse methods now preserve keys on the collection. Review usage of them.
  * app/Console/Commands/GenerateSitemapCommand.php
  * app/Console/Commands/DummyPathsCommand.php
* Since 5.2 the Input facade is no longer registered by default but Input facade is still available using Request facade or $request object within Controllers. Check over files.
  * app/Http/Controllers/ReaderController.php
  * app/Http/Controllers/ReportsController.php
  * app/Http/Controllers/IndexController.php
  * app/Http/Controllers/ApiController.php
  * app/Http/Controllers/UsersController.php
  * app/Http/Controllers/SearchController.php
  * app/filters.php
* Check env etc. and check through leftover 4.2 junk.

* Run Laravel Linter.
* Run php artisan view:clear  and  php artisan route:clear,
