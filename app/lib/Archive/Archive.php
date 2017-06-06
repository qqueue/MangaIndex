<?php

namespace Archive;

interface Archive
{

    public function getFiles();

    public function getEntryStream($entryName);
}
