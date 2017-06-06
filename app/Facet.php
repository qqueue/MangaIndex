<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Facet extends Model
{

    public function series()
    {
        return $this->belongsToMany(\App\Series::class);
    }

    public static function getCreateByName($name)
    {
        $name = trim($name);

        $facet = self::whereName($name)->first();
        if (!$facet) {
            $facet = new self();
            $facet->name = $name;
            $facet->save();
        }

        return $facet;
    }
}
