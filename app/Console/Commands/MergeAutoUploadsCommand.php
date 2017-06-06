<?php

namespace App\Console\Commands;

use App\Lib\Path;
use App\PathRecord;
use App\Series;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/*
* 'where' now performs a loose comparison and the 'strict' parameter
* was removed. Replace "strict" comparisons with whereStrict
* 
* Eloquent scopes now respect the leading bool of scope constraints.
* If using orWhere constraint it won't be converted to a normal 'where'
* anymore.
* 
* JoinClause class has been rewritten. The optional '$where' param
* of the 'on' claise and the 'bindings' property has been removed.
* 
* Query Builder now returns collections instead of plain arrays.
* Plain arrays need to be replaced with collections or chain
* the 'all()' method onto the query to return a plain array.
* 
*/



class MergeAutoUploadsCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'command:merge-auto-uploads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge auto uploads into main series dirs.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $dryRunOpt = $this->option('dry-run');
        if ($dryRunOpt === true) {
            $dryRun = true;
        } elseif ($dryRunOpt === 'true') {
            $dryRun = true;
        } elseif ($dryRunOpt === 'false') {
            $dryRun = false;
        } else {
            $this->error('Invalid value for --dry-run');
            return;
        }

        $sourceDirectories = [
            '/Manga/_Autouploads/AutoUploaded from Assorted Sources'
        ];

        $movedFiles = [];

        // Loop through each auto uploads parent folder
        foreach ($sourceDirectories as $sourceDirectory) {
            $sourcePath = Path::fromRelative($sourceDirectory);
            if (!$sourcePath->exists()) {
                $this->error('Source path does not exist: '.$sourceDirectory);
                continue;
            }

            // Loop through each series dir in the auto uploads folder
            $sourceChildren = $sourcePath->getChildren();
            foreach ($sourceChildren as $sourceChild) {
                $sourceName = $sourceChild->getFilename();

                // Look for matching path records by series name
                $matchedRecords = PathRecord::join('series', 'series.id', '=', 'path_records.series_id')
                    ->join('facet_series', 'facet_series.series_id', '=', 'series.id')
                    ->join('facets', 'facets.id', '=', 'facet_series.facet_id')
                    ->where('facet_series.type', '=', 'title')
                    ->where('facets.name', '=', $sourceName)
                    ->get();

                // Found a match
                if (count($matchedRecords) === 1) {
                    $matchedRecord = $matchedRecords->first();
                    $matchedPath = $matchedRecord->getPath();

                    $seriesChildren = $sourceChild->getChildren();
                    foreach ($seriesChildren as $seriesChild) {
                        if ($seriesChild->isDir()) {
                            $this->error('ERROR: Sub-directory in source series: '.$seriesChild->getPathName());
                            continue;
                        }

                        $srcFile = $seriesChild->getPathName();
                        $dstFile = $matchedPath->getPathName().'/'.$seriesChild->getFilename();

                        if (file_exists($dstFile)) {
                            if ((filesize($srcFile) === filesize($dstFile)) &&
                                (md5_file($srcFile) === md5_file($dstFile))) {
                                $dstFile = Path::fromRelative('/Admin cleanup')->getPathName().'/'.$seriesChild->getFilename();
                            } else {
                                $this->error('ERROR: Destination file already exists: '.$dstFile);
                                continue;
                            }
                        }

                        $movedFiles[] = [
                            'src' => $srcFile,
                            'dst' => $dstFile
                        ];

                        $this->info($srcFile.' -> '.$dstFile);
                    }
                } else {
                    $row = DB::connection('mangaupdates')
                        ->table('namelist')
                        ->where('name', '=', $sourceName)
                        ->orWhere('fsSafeName', '=', $sourceName)
                        ->first();

                    $seriesId = null;
                    if ($row) {
                        $series = Series::where('mu_id', '=', $row->mu_id)->first();

                        if (!$series) {
                            $series = new Series();
                            $series->mu_id = $row->mu_id;
                            $series->save();
                        }

                        $seriesId = $series->id;
                    }

                    $bucket = '# - F';
                    $chr = strtoupper($sourceName[0]);

                    if ($chr >= 'N' && $chr <= 'Z') {
                        $bucket = 'N - Z';
                    } elseif ($chr >= 'G' && $chr <= 'M') {
                        $bucket = 'G - M';
                    }

                    $dstSeries = Path::fromRelative('/Manga/'.$bucket)->getPathName().'/'.$sourceName;

                    if (file_exists($dstSeries)) {
                        $seriesChildren = $sourceChild->getChildren();
                        foreach ($seriesChildren as $seriesChild) {
                            if ($seriesChild->isDir()) {
                                $this->error('ERROR: Sub-directory in source series: ' . $seriesChild->getPathName());
                                continue;
                            }

                            $srcFile = $seriesChild->getPathName();
                            $dstFile = $dstSeries . '/' . $seriesChild->getFilename();

                            if (file_exists($dstFile)) {
                                if ((filesize($srcFile) === filesize($dstFile)) &&
                                    (md5_file($srcFile) === md5_file($dstFile))
                                ) {
                                    $dstFile = Path::fromRelative('/Admin cleanup')
                                                   ->getPathName() . '/' . $seriesChild->getFilename();
                                } else {
                                    $this->error('ERROR: Destination file already exists: ' . $dstFile);
                                    continue;
                                }
                            }

                            $movedFiles[] = [
                                'src' => $srcFile,
                                'dst' => $dstFile
                            ];

                            $this->info($srcFile . ' -> ' . $dstFile);
                        }
                    } else {
                        $movedFiles[] = [
                            'src' => $sourceChild->getPathName(),
                            'dst' => $dstSeries
                        ];

                        $this->info($sourceChild->getPathName() . ' -> ' . $dstSeries);
                    }
                }
            }
        }

        if (!$dryRun) {
            foreach ($movedFiles as $move) {
                try {
                    if (is_file($move['src'])) {
                        $dir = dirname($move['src']);
                        if (!is_dir($dir)) {
                            mkdir($dir, 0777, true);
                        }
                    }

                    rename($move['src'], $move['dst']);
                } catch (ErrorException $exception) {
                    $this->error('ERROR: rename() failed: ' . $seriesChild->getPathName() . ' -> ' . $dstFile . ' ' . $exception->getMessage());
                }
            }
        }

        file_put_contents(storage_path().'/logs/merge-auto-uploads-'.date('Y-m-d-H-i-s'), serialize($movedFiles));

        if (!$dryRun) {
            // Delete empty source folders
            foreach ($sourceDirectories as $sourceDirectory) {
                $sourcePath = Path::fromRelative($sourceDirectory);
                if (!$sourcePath->exists()) {
                    $this->error('Source path does not exist: ' . $sourceDirectory);
                    continue;
                }

                $sourceChildren = $sourcePath->getChildren();
                foreach ($sourceChildren as $sourceChild) {
                    if (count($sourceChild->getChildren()) === 0) {
                        rmdir($sourceChild->getPathName());
                    }
                }
            }
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            //array('example', InputArgument::REQUIRED, 'An example argument.'),
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['dry-run', null, InputOption::VALUE_OPTIONAL, 'Don\'t actually move any files.', false],
        ];
    }
}
