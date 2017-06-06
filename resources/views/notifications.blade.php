@extends('layout')

@section('pageHeading')
    <h1>
        <a href="/">/</a> Series upload notifications ({{ $notifyCount }})
    </h1>
@stop


@section('main')
    @parent
    
    <div class="container">
        @if(count($watched) > 0 || count($notifications) > 0)
            <h2>New uploads</h2>
            @if(count($notifications) === 0)
                <p>
                    You have no new upload notifications!
                </p>
            @else

    <form method="post" action="{{ URL::route('notificationDismiss') }}">
                    {!! Form::token() !!}
                    <table>
                        <thead>
                            <tr>
                                <th>Path</th>
                                <th>Uploaded</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notifications as $notify)
                                <?php $path = $notify->pathRecord->getPath(); ?>
                                <tr @if($notify->dismissed)class="dismissed"@endif>
                                    <td>@if(!is_null($notify->getPath()->getParent()))<a href="{{ $notify->getPath()->getParent()->getUrl() }}">{{ $notify->getPath()->getParent()->getRelativeTop(1) }}</a>@endif<a href="{{ $notify->getUrl() }}" rel="nofollow">{{ $notify->getPath()->getRelativeTop(1) }}</a></td>
                                    <td>{{ $path->getDisplayTime() }}</td>
                                    <td>
                                        @if(!$notify->dismissed)
                                            <button name="notification" value="{{ $notify->id }}" class="button-text">Dismiss</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if($notifyCount > 0)
                            <tfoot>
                                <tr>
                                    <td colspan="2"></td>
                                    <td>
                                        <button id="dismiss-notify-all" name="all" value="true" class="button-text">Dismiss all</button>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </form>

                {!! $notifications->links() !!}
            @endif
        @endif

        @if(count($watched) === 0)
            <p>
                All directories in madokami have RSS (and Atom) feeds, accessible
                through the "Subscribe" link in your web browser
                (Firefox hides it in Bookmarks). Please use that and your favorite
                RSS feed reader or automated downloader for watching series.
            </p>
        @else
            <h2>Watched series</h2>
            <p><a href="/user/watched.opml">Export watched series as OPML (to import into a feed reader)</a></p>
            <form method="post" action="/user/notifications/watch">
                {!! Form::token() !!}
                
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($watched as $series)
                            <tr @if(count($series->pathRecords) !== 1)class="has-paths"@endif>
                                <td>
                                    @if(count($series->pathRecords) === 1)
                                        <?php $path = $series->pathRecords->first()->getPath(); ?>
                                        <a href="{{ $path->getUrl() }}">{{ $series->name }}</a>
                                    @else
                                        <a href="#" class="series-paths-trigger">{{ $series->name }}</a>
                                    @endif
                                </td>
                                <td>
                                    <button id="unwatch" name="series" value="{{ $series->id }}" class="button-text">Unwatch</button>
                                </td>
                            </tr>
                            @if(count($series->pathRecords) !== 1)
                                <tr class="series-paths-expand">
                                    <td colspan="2">
                                        <div class="paths">
                                            <div class="inner">
                                                @foreach($series->pathRecords as $record)
                                                    <?php $path = $record->getPath(); ?>
                                                    <a href="{{ $path->getUrl() }}">{{ $path->getRelativeTop(2) }}</a><br/>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </form>
        @endif
    </div>
@stop
