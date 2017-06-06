<?php

namespace App\Lib;

class MangaUpdates
{

    const BASE_URL = 'https://www.mangaupdates.com/series.html?';
    const USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.111 Safari/537.36';
    
    public static function getManga($muId)
    {
        $url = self::BASE_URL.http_build_query(['id' => $muId]);
        $html = self::getPage($url);
        $doc = phpQuery::newDocumentHTML($html);

        if (!$doc) {
            return false;
        }

        // check page is valid
        if ($doc->find('#main_content .series_content_table')->length() === 0) {
            return false;
        }

        $ret = new stdClass();

        $ret->name = $doc->find('.releasestitle')->text();

        $imgUrl = $doc->find('.sContent img')->attr('src');
        $image = self::saveImage($imgUrl);
        if ($image) {
            $ret->image = $image;
        }

        $catHeaders = $doc->find('.sCat');
        foreach ($catHeaders as $i => $e) {
            $cat = $catHeaders->eq($i)->find('b')->text();
            $content = $catHeaders->eq($i)->next('.sContent');

            if ($cat === 'Description') {
                $ret->description = trim($content->text());
            } elseif ($cat === 'Status in Country of Origin') {
                $ret->origin_status = trim($content->text());
            } elseif ($cat === 'Completely Scanlated?') {
                $ret->scan_status = trim($content->text());
            } elseif ($cat === 'Genre') {
                $genres = self::getManyContent($content, 'a[href*="genresearch"]');
                $ret->genres = $genres;
            } elseif ($cat === 'Categories') {
                $categories = self::getManyContent($content, 'a[href*="category="]');
                $ret->categories = $categories;
            } elseif ($cat === 'Author(s)') {
                $authors = self::getManyContent($content, 'a[href*="authors"]');
                $ret->authors = $authors;
            } elseif ($cat === 'Artist(s)') {
                $artists = self::getManyContent($content, 'a[href*="authors"]');
                $ret->artists = $artists;
            } elseif ($cat === 'Year') {
                $ret->year = trim($content->text());
            } elseif ($cat === 'Associated Names') {
                $titles = explode('<br>', utf8_encode($content->html()));

                foreach ($titles as $index => $title) {
                    $title = html_entity_decode($title);
                    $title = trim($title);

                    if ($title) {
                        $titles[$index] = $title;
                    } else {
                        unset($titles[$index]);
                    }
                }

                $ret->altTitles = $titles;
            } elseif ($cat === 'Related Series') {
                $html = $content->html();
                $lines = explode('<br>', $html);

                $ret->related = [];
                foreach ($content->contents() as $node) {
                    if ($node instanceof DOMElement) {
                        $href = $node->getAttribute('href');
                        if (preg_match('/^series\\.html\\?id=(\\d+)$/', $href, $matches)) {
                            $muId = $matches[1];
                            $type = null;

                            // the relation type text is a standalone text node
                            $next = $node->nextSibling;
                            if ($next instanceof DOMText) {
                                $type = trim($next->wholeText, ' ()');
                            }

                            $ret->related[$muId.'-'.$type] = ['muId' => $muId, 'type' => $type];
                        }
                    }
                }
            }
        }

        return $ret;
    }

    protected static function saveImage($url)
    {
        preg_match('/image\/(.*)$/', $url, $matches);

        if (count($matches) !== 2) {
            return false;
        } else {
            $image = $matches[1];

            $saveDir = config('app.images_path');
            $imagePath = $saveDir.'/'.$image;
            if (!file_exists($imagePath)) {
                copy($url, $imagePath);
            }

            return $image;
        }
    }

    protected static function getManyContent($content, $selector)
    {
        $ret = [];
        $elems = $content->find($selector);
        foreach ($elems as $i => $elem) {
            $ret[] = trim($elems->eq($i)->text());
        }

        return $ret;
    }

    protected static function getPage($url)
    {
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
