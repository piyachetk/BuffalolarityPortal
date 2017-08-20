<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
});

Route::get('/projects', function () {
    return view('allProjects');
});

Route::get('/projects/{project}', function ($project) {
    $projects = App\Project::where('id', '=', $project);
    if ($projects->count() == 0){
        return view('error', ['error' => "Error 404"]);
    }
    return view('project', ['project' => $projects->firstOrFail()]);
});

/*

Route::get('/logout', function(){
    Auth::logout();

    return redirect("/");
});

Route::get('/contact', function(){
    echo "Not implemented";
});

Route::get('/notice', function(){
    echo "Not implemented";
});

Route::group(['middleware' => ['web', 'auth']], function () {
    //Reserved for authenticated users only
});

//Auth::routes();
*/