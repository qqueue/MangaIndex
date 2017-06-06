<?xml version="1.0" encoding="utf-8"?>
<opml version="1.0">
  <head>
    <title>Madokami Watched Series Export</title>
  </head>
  <body>
    <outline text="Madokami - Watched Series">
      @foreach($watched as $series)
        @foreach($series->pathRecords as $path)
             <outline title="{!!htmlspecialchars($series->name, ENT_QUOTES)!!}" type="rss" htmlUrl="{!!htmlspecialchars(URL::to('/'), ENT_QUOTES)!!}{{htmlspecialchars($path->getPath()->getUrl(), ENT_QUOTES)}}" xmlUrl="{!!htmlspecialchars(URL::to('/'), ENT_QUOTES)!!}{{htmlspecialchars($path->getPath()->getUrl(), ENT_QUOTES)}}?t=rss" />
        @endforeach
      @endforeach
    </outline>
  </body>
</opml>
