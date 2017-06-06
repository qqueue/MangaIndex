<?php

namespace App\Lib;

use App\PathRecord;
use Illuminate\Support\Facades\URL;

class Path extends SplFileInfo
{

    public $record;
    protected $hash;

    // file extensions that are safe(ish) for users to download
    protected static $safeFileExtensions = [
        'zip', 'rar', 'cbz', '7z', 'txt', 'jpg', 'png',
        'bmp', 'cbr', 'md5', 'pdf', 'epub', 'jpeg', 'docx',
        'doc', 'odf', 'mobi', 'xz', 'rtf', 'fb2', 'azw3',
    ];

    public static function fromRelative($relPath)
    {
        $path = realpath(config('app.manga_path').$relPath);
        return new Path($path);
    }

    public function getRelative()
    {
        $basePath = rtrim(self::fixSlashes(config('app.manga_path')), '/');
        $absPath = self::fixSlashes($this->getPathname());

        return str_replace($basePath, '', $absPath);
    }

    public function getRelativeTop($count = 2)
    {
        $path = trim($this->getRelative(), '/');
        $bits = explode('/', $path);

        if ($count > $bits) {
            return $this->getRelative();
        } else {
            $bits = array_slice($bits, count($bits) - $count, $count);
            return '/'.implode('/', $bits);
        }
    }

    public static function fixSlashes($path)
    {
        return str_replace('\\', '/', $path);
    }

    public static function hashPath($path)
    {
        $path = self::fixSlashes($path);
        return sha1($path);
    }

    public function getHash()
    {
        if (!$this->hash) {
            $path = $this->getRelative();
            $this->hash = self::hashPath($path);
        }

        return $this->hash;
    }

    public function loadCreateRecord()
    {
        if (!$this->record) {
            $this->record = PathRecord::getCreateForPath($this);
        }

        return $this->record;
    }

    public function loadRecord()
    {
        if (!$this->record) {
            $this->record = PathRecord::getForPath($this);
        }

        return $this->record;
    }
    
    public function getParent()
    {
        if (!$this->isRoot()) {
            return new Path($this->getPath());
        }
    }

    public function getChildren($ignoreDots = true)
    {
        $fileNames = scandir($this->getPathname());

        $ret = [];
        foreach ($fileNames as $fileName) {
            if ($ignoreDots && in_array($fileName, ['.', '..'])) {
                continue;
            }

            if (preg_match("/^\.in\./", $fileName)) {
                 continue;
            }

            $path = new self($this->getPathname().'/'.$fileName);
            $ret[] = $path;
        }

        return $ret;
    }

    public function isRoot()
    {
        return ($this->getRelative() === '');
    }

    public function exists()
    {
        return file_exists($this->getPathname());
    }
    
    public function getDisplayName()
    {
        if ($this->isRoot()) {
            return '/';
        } else {
            return $this->getFilename();
        }
    }

    public function getBreadcrumbs()
    {
        if ($this->isRoot()) { // root fucks with everything...
            $path = Path::fromRelative('/');
            return [$path];
        } else {
            $path = trim($this->getRelative(), '/');
            $bits = explode('/', $path);

            $crumbs = [];
            $crumbs[] = Path::fromRelative('/');

            for ($i = 1; $i <= count($bits); $i++) {
                $path = '/'.implode('/', array_slice($bits, 0, $i));
                $crumbs[] = Path::fromRelative($path);
            }

            return $crumbs;
        }
    }

    public function getUrl()
    {
        $path = trim($this->getRelative(), '/');
        $bits = explode('/', $path);
        $bits = array_map('rawurlencode', $bits);
        $url = implode('/', $bits);

        return '/'.$url;
    }

    public function getDisplaySize()
    {
        $size = $this->getSize();
        return DisplaySize::format($size);
    }

    public function getDisplayTime($short = false)
    {
        $time = $this->getMTime();
        return DisplayTime::format($time, $short);
    }

    public function isSafeExtension()
    {
        $ext = $this->getExtension();
        return ($ext && in_array($ext, self::$safeFileExtensions));
    }

    public function canUseReader()
    {
        $ext = $this->getExtension();
        return in_array($ext, ['zip', 'cbz', 'rar', 'cbr']);
    }

    public function getReaderUrl()
    {
        $rel = $this->getRelative();
        return URL::route('reader', ['path' => rawurlencode($rel)]);
    }

    public function export()
    {
        $data = new stdClass();

        // FS stat-based info
        $data->name = $this->getDisplayName();
        $data->size = $this->getDisplaySize();
        $data->rawSize = $this->getSize();
        $data->rawTime = $this->getMTime();
        $data->url = $this->getUrl();
        $data->isDir = $this->isDir();
        $data->canUseReader = $this->canUseReader();
        // hopefully this is reasonably fast
        $data->mime = $this->system_extension_mime_type($this->getFilename());

        if ($data->canUseReader) {
            $data->readerUrl = $this->getReaderUrl();
        }

        $record = $this->loadCreateRecord();
        if ($record) {
            $data->record = $record->export();
            unset($record);
        }

        return $data;
    }

    function system_extension_mime_types()
    {
        # Returns the system MIME type mapping of extensions to MIME types, as defined in /etc/mime.types.
        $out = [];
        $file = fopen('/etc/mime.types', 'r');
        while (($line = fgets($file)) !== false) {
            $line = trim(preg_replace('/#.*/', '', $line));
            if (!$line) {
                continue;
            }
            $parts = preg_split('/\s+/', $line);
            if (count($parts) == 1) {
                continue;
            }
            $type = array_shift($parts);
            foreach ($parts as $part) {
                $out[$part] = $type;
            }
        }
        fclose($file);
        return $out;
    }

    function system_extension_mime_type($file)
    {
        # Returns the system MIME type (as defined in /etc/mime.types) for the filename specified.
        #
        # $file - the filename to examine
        static $types;
        if (!isset($types)) {
            $types = $this->system_extension_mime_types();
        }
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (!$ext) {
            $ext = $file;
        }
        $ext = strtolower($ext);
        return isset($types[$ext]) ? $types[$ext] : null;
    }
}
