<?php

namespace App\Console\Commands;

use App\Lib\DisplaySize;
use App\PathRecord;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ReverseIndexCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'command:reverse-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete path records that no longer exist on disk';

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
        $dryRun = $this->option('dry-run');

        if (!is_bool($dryRun)) {
            if ($dryRun === 'true') {
                $dryRun = true;
            } elseif ($dryRun === 'false') {
                $dryRun = false;
            } else {
                $this->error($dryRun.' is not a valid value for --dry-run');
                return;
            }
        }

        $pageSize = 1000;
        $page = 0;

        $count = 0;
        $size = 0;

        while (true) {
            $records = PathRecord::take($pageSize)->offset($page * $pageSize)->get();

            foreach ($records as $record) {
                $path = $record->getPath();

                if (!$path->exists()) {
                    $this->line($record->path);

                    $count++;
                    $size += $record->size;

                    if (!$dryRun) {
                        $record->delete();
                    }
                }
            }

            if ($records->count() < $pageSize) {
                break;
            }

            $page++;
        }

        $this->info(sprintf('%d deleted (%s)', $count, DisplaySize::format($size)));
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
            ['dry-run', null, InputOption::VALUE_OPTIONAL, 'Don\'t actually delete path records', false],
        ];
    }
}
