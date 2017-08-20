@extends('layouts.app')

@section("title")
    Projects
@endsection

@section('content')
    @foreach (App\Project::all() as $project)
    <div class="card large" style="max-width: 960px; width: 100%">
        <div class="card-image waves-effect waves-block waves-light">
            <a href="{{$project->id}}/"><img class="activator" src="{{$project->banner}}"></a>
        </div>
        <div class="card-content">
            <span class="card-title activator grey-text text-darken-4">{{$project->name}}</span>
            <p>{{$project->short_description}}</p>
        </div>
        <div class="card-action">
            <a href="{{$project->id}}/">รายละเอียด</a>
        </div>
    </div>
    @endforeach
@endsection
