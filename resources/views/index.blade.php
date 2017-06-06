@extends('layout')

@section('headerstuff')
<link href="?t=rss" type="application/rss+xml" rel="alternate" title="rss" />
<link href="?t=atom" type="application/atom+xml" rel="alternate" title="atom" />
@stop

@section('pageHeading')
    <h1 itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
        @foreach($breadcrumbs as $index => $crumb)
            <a href="{{ $crumb->getUrl() }}" itemprop="url">
                <span itemprop="title">{{ $crumb->getDisplayName() }}</span>
            </a>

            @if($index > 0)
                <span class="slash">/</span>
            @endif
        @endforeach
        ({{ count($children) }})
    </h1>
@stop

@section('main')
    @parent

    <div class="container">
        @if(!$path->isRoot())
            <div id="back-nav">
                <a href="{{ $path->getUrl().'/..' }}">Back</a>
            </div>
        @endif

        <div class="index-container">
            <div class="table-outer">
                <table id="index-table" class="mobile-files-table">
                    <thead>
                        <tr>
                            <th>
                                <a class="order order-{{ $orderDir }} @if($orderMethod === 'name') active @endif" href="?{{ http_build_query([ 'order' => 'name', 'dir' => $invOrderDir ]) }}">Name</a>
                            </th>
                            <th class="size">
                                <a class="order order-{{ $orderDir }} @if($orderMethod === 'size') active @endif" href="?{{ http_build_query([ 'order' => 'size', 'dir' => $invOrderDir ]) }}">Size</a>
                            </th>
                            <th class="time">
                                <a class="order order-{{ $orderDir }} @if($orderMethod === 'time') active @endif" href="?{{ http_build_query([ 'order' => 'time', 'dir' => $invOrderDir ]) }}">Time</a>
                            </th>
                            <th class="tags"></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($children as $child)
                            <tr data-record="{{ $child->record->id }}">
                                <td>
                                    @if($child->isDir)
                                        <a href="{{ $child->url }}" rel="nofollow">{{ $child->name }}/</a>
                                    @else
                                        <a href="{{ $child->url }}" rel="nofollow">{{ $child->name }}</a>
                                    @endif

                                    @if(isset($child->record->series->groupedStaff) && count($child->record->series->groupedStaff) > 0)
                                        <span class="inline-staff">
                                            @foreach($child->record->series->groupedStaff as $index => $staff)
                                                @if($index > 0)
                                                    /
                                                @endif

                                                <a href="{{ Search::url($staff) }}">{{ $staff }}</a>
                                            @endforeach
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($child->isDir)
                                        -
                                    @else
                                        {{ $child->size }}
                                    @endif
                                </td>
                                <td>
                                    {{ DisplayTime::format($child->rawTime) }}
                                </td>
                                <td>
                                    @if(isset($child->record->series->facets->genre))
                                        @foreach($child->record->series->facets->genre as $genre)
                                            <a href="{{ Search::url($genre, 'genre') }}" class="tag">{{ $genre }}</a>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    @if(isset($child->record->locked) && !$child->record->locked && $user)
                                        <a class="report-link" href="#">Report</a>
                                    @endif
                                </td>
                                <td>
                                    @if($child->canUseReader)
                                        <a href="{{ $child->readerUrl }}" target="_blank">Read</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(isset($path->record->series))
                <div class="manga-info-outer" itemscope="" itemtype="http://schema.org/Book">
                    <div class="manga-info">
                        <h2>
                            <span class="title" itemprop="name">{{ $path->record->series->name }}</span>
                            <span class="year" itemprop="datePublished" content="{{ $path->record->series->year }}-01-01">[{{ $path->record->series->year }}]</span><!--
                            -->&nbsp;<a href="{{ $path->record->series->getExternalUrl() }}" target="_blank" class="mu-link icon-link"></a>
                        </h2>
                        <p class="staff" title="Staff">
                            @foreach($groupedStaff as $index => $staff)
                                <a href="{{ Search::url($staff) }}" itemprop="author">{{ $staff }}</a>
                                @if(($index + 1) < count($groupedStaff))
                                    /
                                @endif
                            @endforeach
                        </p>
                        @if($path->record->series->hasImage())
                            <img src="{!! $path->record->series->getImageUrl() !!}" alt width="248" itemprop="image" />
                        @endif

                        @if(count($genres) > 0)
                            <h3>Genres</h3>
                            <div class="genres">
                                @foreach($genres as $genre)
                                    <a class="tag" href="{{ Search::url($genre, 'genre') }}" itemprop="genre">{{ $genre }}</a>
                                @endforeach
                            </div>
                        @endif

                        @if(count($categories) > 0)
                            <h3>Tags</h3>
                            <div class="genres" itemprop="keywords">
                                @foreach($categories as $category)
                                    <a class="tag tag-category" href="{{ Search::url($category, 'category') }}">{{ $category }}</a>
                                @endforeach
                            </div>
                        @endif

                        @if(count($relatedSeries) > 0)
                            <h3>Related series</h3>
                            <ul>
                                @foreach($relatedSeries as $related)
                                    <li><a href="{{ $related->path->getUrl() }}">{{ $related->name }}</a> ({{ $related->type }})
                                @endforeach
                            </ul>
                        @endif

                        <h4>Completely Scanlated?</h4>
                        <span class="scanstatus">{{ $path->record->series->scan_status }}</span>

                        @if($user)
                            <div>
                                @if($userIsWatching)
                                    <a class="button active" id="watch-series" data-series="{{ $path->record->series->id }}">Watching</a>
                                @else
                                    <a class="button" id="watch-series" data-series="{{ $path->record->series->id }}">Watch series</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if(!$path->record->locked || ($user && $user->hasSuper()))
        <div class="container" id="path-edit">
            <h2>Directory details</h2>

            <form action="/path/save" method="post">
                {!! Form::token() !!}
                <input type="hidden" name="record" value="{{ $path->record->id }}">
                <input type="hidden" name="incomplete" value="0">
                <input type="hidden" name="locked" value="0">

                <div id="path-edit-info">
                    @if($path->record->series)
                        <div class="field-row">
                            <button class="button" name="delete" value="1" id="delete-manga">Delete Manga data</button>
                        </div>

                        @if(($user && $user->hasSuper()) || $path->record->series->canUpdateMu())
                            <div class="field-row">
                                <button class="button" name="update" value="1">Update Manga data</button>
                            </div>
                        @endif
                    @else
                        <div class="field-row">
                            <label>MangaUpdates ID</label>
                            <input type="text" name="mu_id" class="input">
                        </div>
                    @endif

                    @if($user && $user->hasSuper())
                        <div class="field-row">
                            <label>
                                Lock directory
                                <input type="checkbox" name="locked" value="1" class="checkbox" @if($path->record->locked) checked @endif>
                            </label>
                        </div>
                    @endif

                    <div class="field-row">
                        <button class="button">Save</button>
                    </div>
                </div>
                <div id="path-edit-comment">
                    <textarea name="comment" class="input" placeholder="Comments">{{ $path->record->comment }}</textarea>
                </div>
            </form>
        </div>
    @endif

    <form id="report-form" method="post" action="{{ URL::route('report') }}">
        {!! Form::token() !!}
    </form>

    <div class="template">
        <table>
            <tr class="report-row">
                <td colspan="5">
                    <div class="expand">
                        <h3>Report file</h3>
                        <textarea name="reason" placeholder="Report reason" class="input" required form="report-form" disabled></textarea>
                        <button class="button" form="report-form" disabled>Submit</button>
                        <a class="button button-report-cancel" href="#">Cancel</a>

                        <input type="hidden" name="record" form="report-form" value disabled>
                    </div>
                </td>
            </tr>
        </table>
    </div>
@stop
