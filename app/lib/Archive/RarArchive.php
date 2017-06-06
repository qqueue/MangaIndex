<?php

namespace Archive;

class RarArchive implements Archive
{

    protected $archive;

    public function __construct(\Path $path)
    {
        $this->archive = \RarArchive::open($path->getPathName());

        if (!$this->archive) {
            throw new Exception('Failed to open archive');
        }
    }

    public function getFiles()
    {
        $entries = $this->archive->getEntries();

        $files = [];
        foreach ($entries as $entry) {
            if (!$entry->isDirectory()) {
                $files[] = $entry->getName();
            }
        }

        natcasesort($files);
        $files = array_values($files); //strip keys

        return $files;
    }

    public function getEntryStream($entryName)
    {
        $entry = $this->archive->getEntry($entryName);

        if (!$entry) {
            throw new Exception('Failed to find entry for file: '.$entryName);
        }

        $stream = $entry->getStream();

        if (!$stream) {
            throw new Exception('Failed to get stream for file: '.$entryName);
        }

        return $stream;
    }
}
