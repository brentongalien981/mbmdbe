<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailySummaryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScheduledTaskController;
use App\Http\Controllers\ScheduledTaskLogController;

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



/** auth */
Route::post('/auth/signIn', [AuthController::class, 'signIn']);



/** users */
Route::post('/users/create', [UserController::class, 'create'])->middleware('bmdauth');



/** roles */
Route::get('/roles/getRoles', [RoleController::class, 'getRoles']);



/** automated-jobs, scheduled-task */
Route::get('/automated-jobs', [ScheduledTaskController::class, 'index'])->middleware('bmdauth');
Route::post('/automated-jobs/execute', [ScheduledTaskController::class, 'execute'])->middleware('bmdauth');
Route::post('/automated-jobs/resetJobStatus', [ScheduledTaskController::class, 'resetJobStatus'])->middleware('bmdauth');



/** automated-job-logs, scheduled-task-logs */
Route::get('/automated-job-logs/read', [ScheduledTaskLogController::class, 'read'])->middleware('bmdauth');



/** daily-summary */
Route::get('/daily-summary/readDailySummaryData', [DailySummaryController::class, 'readDailySummaryData'])->middleware('bmdauth');
Route::get('/daily-summary/readFinanceGraphData', [DailySummaryController::class, 'readFinanceGraphData'])->middleware('bmdauth');




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
