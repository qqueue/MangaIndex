@extends('layout')

@section('pageHeading')
    <h1>
        <a href="/">/</a> Reports ({{ $reportsCount }})
    </h1>
@stop


@section('main')
    @parent

    <div class="message message-info">FTP access is open (see <a href="/READ.txt" rel="nofollow">READ.txt</a>).
        Please contribute by fixing issues rather than just reporting them.</div>

    <div class="container">
        @if(count($reports) > 0)
            <form method="post" action="{{ URL::route('reportDismiss') }}">
                {!! Form::token() !!}
                
                <table id="reports-table">
                    <thead>
                        <tr>
                            <th>Path</th>
                            <th>Reason</th>
                            <th>Time</th>
                            <th>Reported by</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                            <?php $path = $report->getPath(); ?>
                            <tr>
                                <td>
                                    @if(!$path->exists())
                                        {{ $report->path }}
                                        <span class="tag tag-red tag-cell">Deleted</span>
                                    @elseif($path->isDir())
                                        <a href="{{ $path->getUrl() }}">{{ $path->getRelative() }}/</a>
                                    @else
                                        <?php $parent = $path->getParent(); ?>
                                        <a href="{{ $parent->getUrl() }}#{{ $path->getFilename() }}">{{ $path->getRelative() }}</a>
                                    @endif
                                </td>
                                <td>
                                    <span class="report-reason">{{ $report->reason }}</span>
                                </td>
                                <td>
                                    {{ $report->getDisplayTime() }}
                                </td>
                                <td>
                                    @if($report->user)
                                        {{ $report->user->username }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <button name="report" value="{{ $report->id }}" class="button-text">Dismiss</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>

            {!! $reports->links() !!}
        @else
            <p>
                No reports to show!
            </p>
        @endif
    </div>
@stop