<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Notification extends Model
{

    public function pathRecord()
    {
        return $this->belongsTo(\App\PathRecord::class, 'path_record_id');
    }

    public function scopeUnseen($query)
    {
        return $query->where('dismissed', '=', false);
    }

    public static function createForUserRecord(User $user, PathRecord $record)
    {
        $notify = new Notification();
        $notify->user_id = $user->id;
        $notify->path_record_id = $record->id;
        $notify->save();

        return $notify;
    }

    public function dismiss()
    {
        $this->dismissed = true;
        $this->save();
    }

    public function getPath()
    {
        return $this->pathRecord->getPath();
    }

    public function getUrl()
    {
        $path = $this->pathRecord->getPath();
        return URL::route('notificationDownload', ['notification' => $this->id, 'filename' => $path->getBasename()]);
    }
}
