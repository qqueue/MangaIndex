<?php

class MangaUpdates {

    const BASE_URL = 'https://api.mangaupdates.com/v1/series/';
    const USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.111 Safari/537.36';

    public static function getManga($muId) {
        $url = self::BASE_URL . urlencode($muId);
        $content = self::getPage($url);
        $doc = json_decode($content);

        if(!$content) {
            return false;
        }

        $ret = new stdClass();

        $ret->name = $doc->title;

        $imgUrl = $doc->image->url->original;
        $image = self::saveImage($imgUrl);
        if($image) {
            $ret->image = $image;
        }

        $ret->description = $doc->description;
        $ret->origin_status = $doc->status;
        $ret->scan_status = $doc->completed ? 'Yes' : 'No';

        $ret->genres = [];
        foreach($doc->genres as $g) {
            $ret->genres[] = $g->genre;
        }

        $ret->categories = [];
        foreach($doc->categories as $c) {
            $ret->categories[] = $c->category;
        }

        $ret->authors = [];
        $ret->artists = [];
        foreach($doc->authors as $a) {
            if ($a->type === "Author") $ret->authors[] = $a->name;
            if ($a->type === "Artist") $ret->artists[] = $a->name;
        }

        $ret->year = $doc->year;

        $ret->altTitles = [];
        foreach($doc->associated as $t) {
            $ret->altTitles[] = $t->title;
        }

        $ret->related = [];
        foreach($doc->related_series as $s) {
            $ret->related[] = [
                'muId' => $s->related_series_id,
                'type' => $s->relation_type,
            ];
        }

        return $ret;
    }

    protected static function saveImage($url) {
        preg_match('/image\/(.*)$/', $url, $matches);

        if(count($matches) !== 2) {
            return false;
        }
        else {
            $image = $matches[1];

            $saveDir = Config::get('app.images_path');
            $imagePath = $saveDir.'/'.$image;
            if(!file_exists($imagePath)) {
                copy($url, $imagePath);
            }

            return $image;
        }
    }

    protected static function getPage($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        $cookie = $_ENV['MU_COOKIE'];
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        
        $ret = curl_exec($ch);
        curl_close($ch);

        return $ret;
    }

}
