<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class Series extends Model
{

    public static function boot()
    {
        parent::boot();

        Series::updating(function ($series) {
            // delete cache entries for every path record
            foreach ($series->pathRecords as $record) {
                $record->deleteCache();
            }
        });
    }

    public function pathRecords()
    {
        return $this->hasMany(\App\PathRecord::class);
    }

    public function facets()
    {
        return $this->belongsToMany(\App\Facet::class)->withPivot('type');
    }

    public function users()
    {
        return $this->belongsToMany(\App\User::class, 'user_series');
    }

    public function relatedSeries()
    {
        return $this->hasMany(\App\RelatedSeries::class);
    }

    public function getFacetNames($type)
    {
        $facets = $this->facets;
        $ret = [];

        foreach ($facets as $facet) {
            if ($facet->pivot->type === $type) {
                $ret[] = $facet->name;
            }
        }

        return $ret;
    }

    public static function getCreateFromMuId($muId)
    {
        $manga = self::whereMuId($muId)->first();
        if (!$manga) {
            $muData = MangaUpdates::getManga($muId);

            if ($muData) {
                DB::beginTransaction();

                try {
                    $manga = new self();
                    $manga->save();

                    $manga->mu_id = $muId;
                    $manga->importMuData($muData);
                    $manga->save();
                } catch (Exception $e) {
                    DB::rollback();

                    throw $e;
                }

                DB::commit();
            }
        }

        return $manga;
    }

    public function updateMuData()
    {
        $muData = MangaUpdates::getManga($this->mu_id);
        if ($muData) {
            $this->importMuData($muData);
            $this->updated_at = $this->freshTimestamp();
        }

        $this->needs_update = false;
        $this->save();
    }

    private function importMuData($muData)
    {
        $this->name = $muData->name;
        $this->description = $muData->description;
        $this->year = $muData->year;
        $this->origin_status = $muData->origin_status;
        $this->scan_status = $muData->scan_status;

        if (isset($muData->image)) {
            $this->image = $muData->image;
        }

        // remove all related facets
        $this->facets()->detach();

        // authors
        foreach ($muData->authors as $author) {
            $this->addFacetByName($author, 'author');
        }

        // artists
        foreach ($muData->artists as $artist) {
            $this->addFacetByName($artist, 'artist');
        }
        
        // categories
        foreach ($muData->categories as $category) {
            $this->addFacetByName($category, 'category');
        }

        // genres
        foreach ($muData->genres as $genre) {
            $this->addFacetByName($genre, 'genre');
        }

        // titles
        foreach ($muData->altTitles as $title) {
            $this->addFacetByName($title, 'title');
        }

        // add main title
        if (!in_array($muData->name, $muData->altTitles)) {
            $this->addFacetByName($muData->name, 'title');
        }

        // related series
        $this->relatedSeries()->delete(); // remove all previous related series
        if (isset($muData->related)) {
            foreach ($muData->related as $item) {
                $related = new RelatedSeries();
                $related->series_id = $this->id;
                $related->related_mu_id = $item['muId'];
                $related->type = $item['type'];
                $related->save();
            }
        }
    }

    public function addFacetByName($facetName, $type)
    {
        $facet = Facet::getCreateByName($facetName);
        $this->facets()->attach($facet->id, ['type' => $type]);
    }

    public function getExternalUrl()
    {
        return 'https://www.mangaupdates.com/series.html?id='.$this->mu_id;
    }

    public function getGroupedStaff()
    {
        $staff = [];

        foreach ($this->facets as $facet) {
            // if this facet is a staff, and is unqiue
            if (in_array($facet->pivot->type, ['artist', 'author']) && !in_array($facet->name, $staff)) {
                $staff[] = $facet->name;
            }
        }
        
        return $staff;
    }

    // users are allowed to update MU data once every 24 hrs
    public function canUpdateMu()
    {
        return ($this->needs_update || (strtotime($this->updated_at) < strtotime('-1 day')));
    }

    public function getImageUrl()
    {
        if ($this->hasImage()) {
            if (file_exists($this->getImagePath())) {
                return URL::to('/images/'.$this->image);
            } else {
                return 'http://www.mangaupdates.com/image/'.rawurlencode($this->image);
            }
        }
    }

    protected function getImagePath()
    {
        if ($this->hasImage()) {
            $imagesDir = config('app.images_path');
            return $imagesDir.'/'.$this->image;
        }
    }

    public function hasImage()
    {
        return !!$this->image;
    }

    // return an array with series that are "related" to this one based on MU data
    public function getRelated()
    {
        $result = DB::table('related_series')
            ->join('series', 'series.mu_id', '=', 'related_series.related_mu_id')
            ->join('path_records', 'path_records.series_id', '=', 'series.id')
            ->select('series.name', 'path_records.path', 'related_series.type')
            ->where('related_series.series_id', '=', $this->id)
            ->get();

        foreach ($result as $index => &$row) {
            $row->path = Path::fromRelative($row->path);
            
            if (!$row->path->exists()) {
                unset($result[$index]);
            }
        }

        return $result;
    }
}
