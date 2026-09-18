<?php

use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Shared Web Routes
|--------------------------------------------------------------------------
|
| Authentication routes are shared by the frontend and admin areas.
|
*/

Auth::routes(['register' => false, 'verify' => true]);
