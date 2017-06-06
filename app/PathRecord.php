<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PathRecord extends Model
{

    public static function boot()
    {
        parent::boot();

        // observer for on-created notifications
        PathRecord::observe(new PathRecordNotifications());

        PathRecord::updating(function ($record) {
            $record->deleteCache();
        });
    }

    public function deleteCache()
    {
        Cache::tags('paths')->forget($this->path_hash);
    }

    public function series()
    {
        return $this->belongsTo(\App\Series::class);
    }

    public static function getCreateForPath(Path $path)
    {
        $hash = $path->getHash();

        $record = self::wherePathHash($hash)->first();
        if (!$record) {
            $record = new self();

            $fields = self::getPathFields($path);
            foreach ($fields as $field => $value) {
                $record->$field = $value;
            }

            $parent = $path->getParent();
            if ($parent) {
                $parentRecord = $parent->loadCreateRecord();
                $record->parent_id = $parentRecord->id;
            }

            $record->save();
        }

        return $record;
    }

    protected static function getPathFields(Path $path)
    {
        $ret = [];
        $ret['path'] = $path->getRelative();
        $ret['path_hash'] = $path->getHash();
        $ret['directory'] = $path->isDir();
        $ret['size'] = $path->getSize();
        $ret['created'] = date('Y-m-d H:i:s', $path->getCTime());
        $ret['modified'] = date('Y-m-d H:i:s', $path->getMTime());

        return $ret;
    }

    public static function getForPath(Path $path)
    {
        $hash = $path->getHash();
        $record = self::with('series.facets')->wherePathHash($hash)->first();
        return $record;
    }

    public function getPath()
    {
        return Path::fromRelative($this->path);
    }

    public function checkUpdate(Path $path = null)
    {
        if ($path === null) {
            $path = $this->getPath();
        }

        $dirty = false;
        $fields = null;

        // or if any of the fields have changed
        if (!$dirty) {
            $fields = self::getPathFields($path);
            foreach ($fields as $field => $value) {
                if ($this->$field != $value) {
                    $dirty = true;
                    break;
                }
            }
        }

        // all paths apart from root should have a parent assigned
        if (!$this->parent_id && !$path->isRoot()) {
            $dirty = true;
        }

        if ($dirty) {
            foreach ($fields as $field => $value) {
                $this->$field = $value;
            }

            $parent = $path->getParent();
            if ($parent) {
                $parentRecord = $parent->loadCreateRecord();
                $this->parent_id = $parentRecord->id;
            }

            $this->save();

            return true;
        }

        return false;
    }

    public function export()
    {
        // if we've got a series, then load series and facets data
        if ($this->series_id > 0) {
            $this->load('series.facets');
        }

        // convert to stdClass for easy use
        $recordData = (object)$this->toArray();

        // process series data
        if (isset($recordData->series)) {
            $recordData->series = (object)$recordData->series;

            // export facets
            if (isset($recordData->series->facets)) {
                $recordData->series->facets = self::processExportedFacets($recordData->series->facets);
                $recordData->series->groupedStaff = $this->series->getGroupedStaff();
            }
        }
        
        return $recordData;
    }

    // group all facets by type
    protected static function processExportedFacets($facets)
    {
        $ret = new stdClass();

        foreach ($facets as $facet) {
            $type = $facet['pivot']['type'];

            if (!isset($ret->$type)) {
                $ret->$type = [];
            }

            $ret->{$type}[] = $facet['name'];
        }

        return $ret;
    }
}
