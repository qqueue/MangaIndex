<?php

namespace Archive;

class ZipArchive implements Archive
{

    protected $archive;

    public function __construct(\Path $path)
    {
        $this->archive = new \ZipArchive();

        $result = $this->archive->open($path->getPathName());

        if (!$result) {
            throw new Exception('Failed to open archive');
        }
    }

    public function getFiles()
    {
        $files = [];
        for ($i = 0; $i < $this->archive->numFiles; $i++) {
            $stat = $this->archive->statIndex($i);
            if ($stat['size'] !== 0 && $stat['crc'] !== 0) { // not directory
                $file = $this->archive->getNameIndex($i);
                $files[] = $file;
            }
        }

        natcasesort($files);
        $files = array_values($files); //strip keys

        return $files;
    }

    public function getEntryStream($entryName)
    {
        $stream = $this->archive->getStream($entryName);

        if (!$stream) {
            throw new Exception('Failed to get stream for file: '.$entryName);
        }

        return $stream;
    }
}
