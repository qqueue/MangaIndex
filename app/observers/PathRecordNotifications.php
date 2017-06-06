<?php

namespace App\Observers;

use App\Notification;

class PathRecordNotifications
{

    public function created(PathRecord $record)
    {
        $this->checkNotifications($record);
    }

    public function saved(PathRecord $record)
    {
        $path = $record->getPath();

        // don't bother on paths older than a day
        if ($path->getMTime() < strtotime('-1 day')) {
            return;
        }

        $this->checkNotifications($record);
    }

    protected function checkNotifications(PathRecord $record)
    {
        $path = $record->getPath();
        $parent = $path->getParent();

        // probably in the root dir
        if ($parent === null) {
            return;
        }

        $rootRecord = $parent->loadRecord();
        if (!$rootRecord) {
            return;
        }

        // check if parent has a series assigned
        if (!$rootRecord->series) {
            return;
        }

        // find users who are linked to this series (via user_series) and who don't already
        // have a notification for this path record
        $users = $rootRecord->series->users()->whereHas('notifications', function ($q) use ($record) {
            $q->where('path_record_id', '=', $record->id);
        }, '<', 1)->get();

        // add notifications for users
        foreach ($users as $user) {
            Notification::createForUserRecord($user, $record);
        }
    }
}
