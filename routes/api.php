<?php

use App\Http\Controllers\Api\AnnouncementApiController;
use App\Http\Controllers\Api\AnnouncementCategoryApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchApiController;
use App\Http\Controllers\Api\GsheetLinkApiController;
use App\Http\Controllers\Api\JobLevelApiController;
use App\Http\Controllers\Api\LiveAbsentApiController;
use App\Http\Controllers\Api\OrganizationApiController;
use App\Http\Controllers\Api\PayslipApiController;
use App\Http\Controllers\Api\PersonApiController;
use App\Http\Controllers\Api\PinLocationApiController;
use App\Http\Controllers\Api\PositionApiController;
use App\Http\Controllers\Api\PushNotificationApiController;
use App\Http\Controllers\Api\ReligionApiController;
use App\Models\Religion;
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


Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login');
Route::post('/payslip/post', [PayslipApiController::class, 'post']);
Route::post('/push/notif', [PushNotificationApiController::class, 'sendMessage']);
Route::get('attendance/summary', [AttendanceApiController::class, 'getSummaryReport']);
Route::get('attendance/auth', [AttendanceApiController::class, 'mekariOauth2']);

Route::group(['middleware' => 'auth_login'], function () {
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('register', [AuthController::class, 'register']);
    Route::delete('logout', [AuthController::class, 'logout']);
    Route::post('password/change', [AuthController::class, 'changePassword']);

    Route::get('school/calendar', [GsheetLinkApiController::class, 'getSchoolCalendar']);
    Route::get('newsletter', [GsheetLinkApiController::class, 'getNewsletter']);
    Route::get('kpi', [GsheetLinkApiController::class, 'kpi']);

    Route::get('person/email/{email}', [PersonApiController::class, 'byEmail']);
    Route::get('person/personal/{companyId}', [PersonApiController::class, 'getPersonalData']);
    Route::resource('person', PersonApiController::class);

    Route::get('attendance/schedule/{user_id}', [AttendanceApiController::class, 'getUserScheduleById']);
    Route::get('attendance/history', [AttendanceApiController::class, 'getHistory']);
    Route::get('attendance/list', [AttendanceApiController::class, 'liveAttendanceList']);

    Route::resource('attendance', AttendanceApiController::class);

    Route::get('absent/filter', [LiveAbsentApiController::class, 'filterByUser']);
    Route::get('absent/city/{city}', [LiveAbsentApiController::class, 'getCity']);
    Route::resource('absent', LiveAbsentApiController::class);

    Route::resource('payslip', PayslipApiController::class);

    Route::resource('category', AnnouncementCategoryApiController::class);

    Route::resource('branch', BranchApiController::class);

    Route::resource('organization', OrganizationApiController::class);

    Route::resource('position', PositionApiController::class);

    Route::resource('level', JobLevelApiController::class);

    Route::resource('announcement', AnnouncementApiController::class);

    Route::resource('location', PinLocationApiController::class);

    Route::resource('religion', ReligionApiController::class);
});
