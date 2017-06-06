<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
  <channel>
    <title>{!!htmlspecialchars( $pageTitle , ENT_QUOTES)!!} - Madokami</title>
    <link>{!!htmlspecialchars(URL::to('/'), ENT_QUOTES)!!}{{htmlspecialchars( $path->getUrl() , ENT_QUOTES)}}</link>
    <description>{!!htmlspecialchars( $path->getUrl() , ENT_QUOTES)!!} - Madokami</description>
    <pubDate>{!!htmlspecialchars( (new DateTime(date('Y-m-d H:i:s', $updated)))->format(DateTime::RFC822), ENT_QUOTES)!!}</pubDate>

    @foreach($children as $child)<item>
      <title>{!!htmlspecialchars( $child->name , ENT_QUOTES)!!}</title>
      <link>{!!htmlspecialchars(URL::to('/'), ENT_QUOTES)!!}{{htmlspecialchars( $child->url , ENT_QUOTES)}}</link>
      @if(!$child->isDir)
      <enclosure url="{!!htmlspecialchars(URL::to('/'), ENT_QUOTES)!!}{{htmlspecialchars( $child->url , ENT_QUOTES)}}" type="{!!htmlspecialchars($child->mime, ENT_QUOTES)!!}" length="{!!htmlspecialchars($child->rawSize, ENT_QUOTES)!!}" />
      @endif
      <guid>{!!htmlspecialchars(URL::to('/'), ENT_QUOTES)!!}{{htmlspecialchars( $child->url , ENT_QUOTES)}}?length={!!htmlspecialchars( $child->rawSize , ENT_QUOTES)!!}&amp;mtime={!!htmlspecialchars( $child->rawTime, ENT_QUOTES)!!}</guid>
      <pubDate>{!!htmlspecialchars(date('r', $child->rawTime), ENT_QUOTES)!!}</pubDate>
      <description><![CDATA[<a href="{!!htmlspecialchars(URL::to('/'), ENT_QUOTES)!!}{{htmlspecialchars( $path->getUrl() , ENT_QUOTES)}}">{!!htmlspecialchars( $path->getUrl() , ENT_QUOTES)!!}</a>/<a href="{!!htmlspecialchars(URL::to('/'), ENT_QUOTES)!!}{{htmlspecialchars( $child->url , ENT_QUOTES)}}" type="{!!htmlspecialchars($child->mime, ENT_QUOTES)!!}" length="{!!htmlspecialchars($child->rawSize, ENT_QUOTES)!!}">{!!htmlspecialchars( $child->name , ENT_QUOTES)!!}</a>]]></description>
    </item>
    @endforeach
</channel>
</rss>
