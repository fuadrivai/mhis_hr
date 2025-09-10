<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeScheduleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobLevelController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\PinLocationController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ReligionController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\UserController;
use Faker\Provider\ar_EG\Person;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => 'prevent-back-history'], function () {
    Route::get('/login', [AuthController::class, 'index'])->middleware('guest')->name('login');
    Route::resource('attendance', AttendanceController::class);
    Route::post('/login', [AuthController::class, 'authenticate']);


    Route::group(['middleware' => 'auth'], function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/', [HomeController::class, 'index']);

        Route::put('/user/reset', [UserController::class, 'resetPassword']);
        Route::resource('user', UserController::class);

        Route::resource('location', PinLocationController::class);

        Route::post('employee/import', [EmployeeController::class, 'import_excel']);
        Route::get('employee/filter', [EmployeeController::class, 'filterLocation']);
        Route::resource('employee', EmployeeController::class);
        Route::resource('scheduler', EmployeeScheduleController::class);


        Route::get('shift/get', [ShiftController::class, 'get']);

        Route::group(['prefix' => 'setting'], function () {
            Route::resource('bank', BankController::class);
            Route::resource('religion', ReligionController::class);
            Route::resource('level', JobLevelController::class);
            Route::resource('position', PositionController::class);
            Route::resource('organization', OrganizationController::class);
            Route::resource('branch', BranchController::class);

            Route::resource('schedule', ScheduleController::class);
            Route::resource('shift', ShiftController::class);

            Route::resource('bank', BankController::class);
        });

        Route::group(['prefix' => 'profile'], function () {
            Route::get('personal/{id}', [EmployeeController::class, 'personal']);
            Route::put('personal', [PersonalController::class, 'update']);

            Route::get('employment/{id}', [EmployeeController::class, 'employment']);
            Route::get('education/{id}', [EmployeeController::class, 'education']);
            Route::get('portofolio/{id}', [EmployeeController::class, 'portofolio']);
            Route::get('payrol-info/{id}', [EmployeeController::class, 'payrol_info']);
            Route::get('attendance/{id}', [EmployeeController::class, 'attendance']);
            Route::get('timeoff/{id}', [EmployeeController::class, 'timeoff']);
        });

        Route::resource('signature', SignatureController::class);
    });
});
