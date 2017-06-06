<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">

  <title>{!!htmlspecialchars( $pageTitle , ENT_QUOTES)!!}</title>
  <link rel="self" href="{!!htmlspecialchars(URL::to('/'), ENT_QUOTES)!!}{{htmlspecialchars( $path->getUrl() , ENT_QUOTES)}}?t=atom" />
  <link href="{!!htmlspecialchars(URL::to('/'), ENT_QUOTES)!!}{{htmlspecialchars( $path->getUrl() , ENT_QUOTES)}}" />
  <id>{!!htmlspecialchars(URL::to('/'), ENT_QUOTES)!!}{{htmlspecialchars( $path->getUrl() , ENT_QUOTES)}}</id>
  <updated>{!!htmlspecialchars( (new DateTime(date('Y-m-d H:i:s', $updated)))->format(DateTime::ISO8601), ENT_QUOTES)!!}</updated>

  @foreach($children as $child)@if(!$child->isDir)<entry>
    <title>{!!htmlspecialchars( $child->name , ENT_QUOTES)!!}</title>
    <link href="{!!htmlspecialchars(URL::to('/'), ENT_QUOTES)!!}{{htmlspecialchars( $child->url , ENT_QUOTES)}}" />
                            <link href="{!!htmlspecialchars(URL::to('/'), ENT_QUOTES)!!}{{htmlspecialchars( $child->url , ENT_QUOTES)}}" rel="enclosure" type="{!!htmlspecialchars($child->mime, ENT_QUOTES)!!}" length="{!!htmlspecialchars($child->rawSize, ENT_QUOTES)!!}" />
    <id>{!!htmlspecialchars(URL::to('/'), ENT_QUOTES)!!}{{htmlspecialchars( $child->url , ENT_QUOTES)}}?length={!!htmlspecialchars( $child->rawSize , ENT_QUOTES)!!}&amp;mtime={!!htmlspecialchars( $child->rawTime, ENT_QUOTES)!!}</id>
    <updated>{!!htmlspecialchars( (new DateTime(date('Y-m-d H:i:s', $child->rawTime)))->format(DateTime::ISO8601), ENT_QUOTES)!!}</updated>
  </entry>
 @endif @endforeach

</feed>

