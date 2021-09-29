<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Models\Permission;
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

Route::get('/',function(){
 return response('hello');
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Route::resource('/department',DepartmentController::class)->middleware('auth');

Route::middleware('auth:api')->group(function () {
    Route::resource('/department', DepartmentController::class);
    Route::resource('/role', RoleController::class);
    Route::resource('/permission', PermissionController::class);
    Route::post('/assign/permission', [PermissionController::class, 'assign_permission']);
    Route::delete('/logout', [AuthController::class, 'logout']);
    Route::resource('user', UserController::class);
    Route::resource('project', ProjectController::class);
    Route::resource('docs', DocController::class);
    Route::get('/doc/download/{id}',[DocController::class,'download_file']);
    Route::get('tasks/my/',[TaskController::class,'my_tasks']);
    Route::resource('tasks',TaskController::class);
    Route::post('tasks/status/{id}',[TaskController::class,'change_status']);
    Route::post('tasks/resource/{id}',[TaskController::class,'assign_resource']);
    Route::resource('resources',ResourceController::class);
});
