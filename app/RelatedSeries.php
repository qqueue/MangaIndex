<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RelatedSeries extends Model
{

    public function series()
    {
        return $this->belongsTo(\App\Series::class);
    }
}
