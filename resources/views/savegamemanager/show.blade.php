@extends('layouts.app')
@section('pagetitle', 'savegames')
@section('content')
    <div class="container">
        <div class="row">
            <div class="page-header">
                <h1>{{ trans('_partials.footer.impressum') }}</h1>
                {!! Breadcrumbs::render('impressum') !!}
            </div>
        </div>
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-header">

                </div>
                <ul class="media-list">
                    @foreach($savegames as $s)
                        <li class="media">
                            <div class="media-body active">
                                <div class="media">
                                    <a class="pull-left" href="#">
                                        <img width="32px" class="media-object img-rounded" src="{{ $s['data'][100]['char1_face']['url'] }}">
                                    </a>
                                    <div class="media-body">
                                        Slot: {{ $s['slot'] }}<span> • </span>Name: {{ $s['data'][100]['char1_name']['data'] }}
                                        <br>
                                        <small class="text-muted">
                                            LEVEL: {{ $s['data'][100]['char1_level']['data'] }}
                                            <span> • </span>
                                            HP: {{ $s['data'][100]['char1_hp']['data'] }}
                                            <span> • </span>
                                            DATE: {{ $s['date'] }}
                                        </small>
                                        <hr>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection