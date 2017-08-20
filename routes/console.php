<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

/*
Artisan::command('createUser {firstName} {lastName} {email} {password}', function($firstName, $lastName, $email, $password){
    \Illuminate\Support\Facades\DB::table('users')->insert(['email' => $email, 'firstname' => $firstName, 'lastname' => $lastName, 'password' => bcrypt($password)]);
    $this->info('Done!');
});

Artisan::command('deleteUser {email}', function($email){
    \Illuminate\Support\Facades\DB::table('users')->where('email', '=', $email)->delete();
    $this->info('Done!');
});

Artisan::command('resetUsers', function(){
    if ($this->confirm('Are you sure you wanted to do this?')) {
        \Illuminate\Support\Facades\DB::table('users')->truncate();
        \Illuminate\Support\Facades\DB::raw("ALTER TABLE users AUTO_INCREMENT = 1");
        $this->info('Done!');
    }
});
*/