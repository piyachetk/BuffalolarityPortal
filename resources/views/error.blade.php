@extends('layouts.app')

@section("title")
    Error
@endsection

@section('content')
    <div class="z-depth-1 card-panel" style="max-width:800px;margin: 3rem auto auto;">
        <div class="row">
            <div class="col s12 m3 center-align">
                <br />
                <i class="large material-icons red-text">error</i><br/>
            </div>
            <div class="col s12 m9">
                <h4>เกิดข้อผิดพลาด</h4>
                {!! (!isset($error) || trim($error) === '') ? "Undefined Error" : $error !!}<br/><br/>
            </div>
        </div>
    </div>
@endsection
