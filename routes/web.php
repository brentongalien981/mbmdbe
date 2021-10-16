<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DailySummaryController;
use App\Http\Controllers\MyTestController;

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


// BMD-ON-STAGING: Comment-out
Route::get('/test', [MyTestController::class, 'read']);
Route::get('/mytest/get-http-info', [MyTestController::class, 'getHttpInfo']);


// BMD-ON-STAGING: Comment-out
// Route::get('/', function () {
//     return view('welcome');
// });
