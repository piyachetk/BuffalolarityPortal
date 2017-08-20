@extends('layouts.app')

@section("title")
    {{$project->name}}
@endsection

@section("pre-content")
    <div class="grey darken-4 white-text" style="padding-top:60px;padding-bottom:40px;line-height:1.5rem;">
        <div class="container center">
            <h1>{{$project->name}}</h1>
            <br/>
            <img src="{{$project->icon}}" style="max-width: 256px; max-height: 256px; width: 100%; height: 100%; border-radius: 20%;"/>
            <br/>
            <br/>
            <a class="btn-floating btn-large waves-effect waves-light blue" style="margin-top: 20px;" href="{{ $project->main_url }}">
                <i class="material-icons">launch</i>
            </a>
            <br/>
            <br/>
        </div>
    </div>
@endsection

@section("content")
    <br/>
    <div class="container" style="max-width: 960px; width: 100%">
        <div class="section">

            <div class="z-depth-1 card-panel white" style="max-width:800px; margin: auto auto auto;">
                <h5 class="center">รายละเอียด</h5>

                {!! $project->full_description !!}
            </div>

        </div>
    </div>

    <div class="container" style="max-width: 960px; width: 100%">
        <div class="section">

            <div class="z-depth-1 card-panel white" style="max-width:800px; margin: auto auto auto;">
                <img src="{{ $project->banner }}" style="width: 100%;"/>
            </div>

        </div>
    </div>
@endsection