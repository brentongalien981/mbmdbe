<?php

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



/** users */
Route::post('/users/create', [UserController::class, 'create']);



/** roles */
Route::get('/roles/getRoles', [RoleController::class, 'getRoles']);



/* test */
// BMD-FOR-DEBUG
// fruitcake/laravel-cors middleware setup.
// BMD-ON-STAGING: Comment-out.
// BMD-TAGS: test, testing, debug, tinker, cors, http, https, auth
Route::get('/test', function (Request $request) {
    return [
        'isResultOk' => true,
        'url' => '/test',
        'comment' => 'random shit bruh'
    ];
});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
