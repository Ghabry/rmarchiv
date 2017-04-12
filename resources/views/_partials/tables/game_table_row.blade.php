<tr>
    <td>
        @if($game->gamefiles->count() > 0)
            <span class='typeiconlist'>
                <span class='typei type_{{ $game->gamefiles->first()->gamefiletype->short }}'
                      title='{{ $game->gamefiles->first()->gamefiletype->title }}'>{{ $game->gamefiles->first()->gamefiletype->title }}</span>
            </span>
        @endif
        <span class="platformiconlist">
            <a href="{{ route('maker.show', $game->maker->id) }}">
                <span class="typei type_{{ $game->maker->short }}" title="{{ $game->maker->title }}">
                    {{ $game->maker->title }}
                </span>
            </a>
        </span>
        <span class='prod'>
            <a href='{{ url('games', $game->id) }}'>
                {{ $game->title }}
                @if($game->subtitle)
                    <small> - {{ $game->subtitle }}</small>
                @endif
                <span><img src="/assets/lng/16/{{ strtoupper($game->language->short) }}.png"
                           title="{{ $game->language->name }}"></span>
            </a>
        </span>
        @if($game->cdcs->count() > 0)
            <div class="cdcstack">
                <img src="/assets/cdc.png" title="{{ trans('app.cdc.title') }}" alt="{{ trans('app.cdc.title') }}">
            </div>
        @endif
    </td>
    <td>
        {!! \App\Helpers\DatabaseHelper::getDevelopersUrlList($game->id) !!}
        {{-- <a href="{{ url('developer', $game->developerid) }}">{{ $game->developername }}</a> --}}
    </td>
    <td class='date'>
        @if(\Carbon\Carbon::parse($game->release_date)->year != -1 )
            {{ $game->release_date }}
        @else
            {{ \Carbon\Carbon::parse(\App\Helpers\DatabaseHelper::getReleaseDateFromGameId($game->id))->toDateString() }}
        @endif
    </td>
    <td class='date'>
        <time datetime='{{ $game->created_at }}'
              title='{{ $game->created_at }}'>{{ \Carbon\Carbon::parse($game->created_at)->diffForHumans() }}</time>
    </td>
    <td class='votes'>{{ $game->voteup or 0 }}</td>
    <td class='votes'>{{ $game->votedown or 0 }}</td>
    <td class='votes'>{{ number_format(floatval($game->avg), 2) }}&nbsp;
        @if($game->avg > 0)
            <img src='/assets/rate_up.gif' alt='{{ trans('app.games.table.rate_up') }}'/>
        @elseif($game->avg == 0)
            <img src='/assets/rate_neut.gif' alt='{{ trans('app.games.table.rate_neut') }}'/>
        @elseif($game->avg < 0)
            <img src='/assets/rate_down.gif' alt='{{ trans('app.games.table.rate_down') }}'/>
        @endif
    </td>
    @php
        $perc = \App\Helpers\MiscHelper::getPopularity($game->views, $maxviews);
    @endphp
    <td>
        <div class='innerbar_solo' style='width: {{ $perc }}%' title='{{ number_format($perc, 2) }}%'>
            <span>{{ $perc }}</span></div>
    </td>
    <td>{{ $game->comments }}</td>
</tr>