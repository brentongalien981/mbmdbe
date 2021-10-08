<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailySummaryController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\DispatchStatusController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\PurchaseStatusController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScheduledTaskController;
use App\Http\Controllers\ScheduledTaskLogController;
use App\Http\Controllers\ShippingController;

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



/** orders */
Route::get('/orders', [OrderController::class, 'index'])->middleware('bmdauth');
Route::get('/orders/show', [OrderController::class, 'show'])->middleware('bmdauth');
Route::post('/orders/update', [OrderController::class, 'update'])->middleware('bmdauth');
Route::post('/orders/store', [OrderController::class, 'store'])->middleware('bmdauth');
Route::post('/orders/refresh', [OrderController::class, 'refresh'])->middleware('bmdauth');



/** order-items */
Route::post('/order-items/store', [OrderItemController::class, 'store'])->middleware('bmdauth');
Route::post('/order-items/update', [OrderItemController::class, 'update'])->middleware('bmdauth');
Route::post('/order-items/associateToPurchases', [OrderItemController::class, 'associateToPurchases'])->middleware('bmdauth');



/** order-statuses */
Route::get('/order-statuses', [OrderStatusController::class, 'index'])->middleware('bmdauth');


/** purchases */
Route::get('/purchases', [PurchaseController::class, 'index'])->middleware('bmdauth');
Route::get('/purchases/show', [PurchaseController::class, 'show'])->middleware('bmdauth');
Route::post('/purchases/store', [PurchaseController::class, 'store'])->middleware('bmdauth');
Route::post('/purchases/update', [PurchaseController::class, 'update'])->middleware('bmdauth');



/** purchase-items */
Route::post('/purchase-items/store', [PurchaseItemController::class, 'store'])->middleware('bmdauth');
Route::post('/purchase-items/update', [PurchaseItemController::class, 'update'])->middleware('bmdauth');



/** purchase-statuses */
Route::get('/purchase-statuses', [PurchaseStatusController::class, 'index'])->middleware('bmdauth');



/** dispatches */
Route::get('/dispatches', [DispatchController::class, 'index'])->middleware('bmdauth');
Route::post('/dispatches/store', [DispatchController::class, 'store'])->middleware('bmdauth');
Route::post('/dispatches/addOrderToDispatch', [DispatchController::class, 'addOrderToDispatch'])->middleware('bmdauth');
Route::get('/dispatches/show', [DispatchController::class, 'show'])->middleware('bmdauth');
Route::post('/dispatches/removeOrderFromDispatch', [DispatchController::class, 'removeOrderFromDispatch'])->middleware('bmdauth');
Route::post('/dispatches/saveEpBatchPickupInfo', [DispatchController::class, 'saveEpBatchPickupInfo'])->middleware('bmdauth');
Route::post('/dispatches/buyPickupRate', [DispatchController::class, 'buyPickupRate'])->middleware('bmdauth');
Route::post('/dispatches/cancelPickup', [DispatchController::class, 'cancelPickup'])->middleware('bmdauth');
Route::post('/dispatches/update', [DispatchController::class, 'update'])->middleware('bmdauth');
Route::post('/dispatches/generateBatchLabels', [DispatchController::class, 'generateBatchLabels'])->middleware('bmdauth');



/** dispatch-statuses */
Route::get('/dispatch-statuses', [DispatchStatusController::class, 'index'])->middleware('bmdauth');



/** shipping */
Route::post('/shipping/checkPossibleShipping', [ShippingController::class, 'checkPossibleShipping'])->middleware('bmdauth');
Route::post('/shipping/buyShippingLabel', [ShippingController::class, 'buyShippingLabel'])->middleware('bmdauth');





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