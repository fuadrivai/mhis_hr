<?php

use App\Http\Controllers\ApprovalRequestController;
use App\Http\Controllers\ApprovalRuleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeScheduleController;
use App\Http\Controllers\EmploymentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InternalDocumentController;
use App\Http\Controllers\JobLevelController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\PinLocationController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ReligionController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\TimeOffController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KpiTemplateController;
use App\Http\Controllers\EmployeeKpiController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AnnouncementCategoryController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ReprimandController;
use App\Http\Controllers\ReprimandTypeController;
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
    Route::get('/login', [AuthController::class, 'index'])->middleware('guest')->name('login-page');
    Route::get('live-attendance', [AttendanceController::class, 'liveAttendance']);
    Route::resource('attendance', AttendanceController::class);
    Route::post('/login', [AuthController::class, 'authenticate']);


    Route::group(['middleware' => 'auth'], function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/', [HomeController::class, 'index']);
        Route::get('clockin', [AttendanceLogController::class, 'clockin']);

        Route::resource('pin-location', PinLocationController::class);
        Route::resource('scheduler', EmployeeScheduleController::class);
        Route::get('shift/get', [ShiftController::class, 'get']);

        Route::prefix('user')->name('user.')->group(function () {
            Route::put('/reset', [UserController::class, 'resetPassword']);
            Route::resource('/', UserController::class)->parameters([
                '' => 'user'
            ]);
        });

        Route::group(['prefix' => 'announcement'], function () {
            Route::resource('category', AnnouncementCategoryController::class);
            Route::resource('/', AnnouncementController::class);
        });
        Route::group(['prefix' => 'employee'], function () {
            Route::post('import', [EmployeeController::class, 'import_excel']);
            Route::get('filter', [EmployeeController::class, 'filterLocation']);
            Route::POST('{employeeId}/document/upload', [EmployeeController::class, 'documentUpload']);
            Route::post('deactivate', [EmployeeController::class, 'deactivate']);
            Route::post('face/register', [EmployeeController::class, 'registerFace'])->name('registerFace');
            Route::resource('reprimand', ReprimandController::class);
            Route::resource('/', EmployeeController::class);
        });
        

        Route::group(['prefix' => 'setting'], function () {
            Route::resource('bank', BankController::class);
            Route::resource('religion', ReligionController::class);
            Route::resource('level', JobLevelController::class);
            Route::resource('position', PositionController::class);
            Route::resource('organization', OrganizationController::class);
            Route::resource('branch', BranchController::class);

            Route::resource('schedule', ScheduleController::class);
            Route::resource('shift', ShiftController::class);

            Route::get('timeoff/datatable', [TimeOffController::class, 'dataTable']);
            Route::get('timeoff/preview', [TimeOffController::class, 'preview']);
            Route::resource('timeoff', TimeOffController::class);

            Route::get('location/employee/filter', [LocationController::class, 'filterEmployee']);
            Route::resource('location', LocationController::class);

            Route::get('approval/employee/active', [ApprovalRuleController::class, 'getActiveEmployees']);
            Route::resource('approval', ApprovalRuleController::class);

            Route::resource('reprimand-type', ReprimandTypeController::class);
            Route::post('kpi-template/{kpi_template}/copy', [KpiTemplateController::class, 'copy'])->name('kpi-template.copy');
            Route::resource('kpi-template', KpiTemplateController::class)->parameters([
                'kpi-template' => 'kpi_template'
            ]);
            
            Route::put('academic-year/{id}/active', [AcademicYearController::class, 'setActive'])->name('academic-year.active');
            Route::resource('academic-year', AcademicYearController::class)->parameters([
                'academic-year' => 'academic_year'
            ]);

            // Lesson Plan Settings
            Route::get('lesson-plan', [\App\Http\Controllers\LessonPlanSettingController::class, 'index'])->name('lesson-plan-setting.index');
            Route::post('lesson-plan/class', [\App\Http\Controllers\LessonPlanSettingController::class, 'storeClass'])->name('lesson-plan-setting.class.store');
            Route::delete('lesson-plan/class/{id}', [\App\Http\Controllers\LessonPlanSettingController::class, 'destroyClass'])->name('lesson-plan-setting.class.destroy');
            Route::post('lesson-plan/category', [\App\Http\Controllers\LessonPlanSettingController::class, 'storeCategory'])->name('lesson-plan-setting.category.store');
            Route::delete('lesson-plan/category/{id}', [\App\Http\Controllers\LessonPlanSettingController::class, 'destroyCategory'])->name('lesson-plan-setting.category.destroy');
            Route::post('lesson-plan/subject', [\App\Http\Controllers\LessonPlanSettingController::class, 'storeSubject'])->name('lesson-plan-setting.subject.store');
            Route::delete('lesson-plan/subject/{id}', [\App\Http\Controllers\LessonPlanSettingController::class, 'destroySubject'])->name('lesson-plan-setting.subject.destroy');
            Route::post('lesson-plan/approver', [\App\Http\Controllers\LessonPlanSettingController::class, 'storeApprover'])->name('lesson-plan-setting.approver.store');
            Route::delete('lesson-plan/approver/{id}', [\App\Http\Controllers\LessonPlanSettingController::class, 'destroyApprover'])->name('lesson-plan-setting.approver.destroy');
            Route::post('lesson-plan/monitor', [\App\Http\Controllers\LessonPlanSettingController::class, 'storeMonitor'])->name('lesson-plan-setting.monitor.store');
            Route::delete('lesson-plan/monitor/{id}', [\App\Http\Controllers\LessonPlanSettingController::class, 'destroyMonitor'])->name('lesson-plan-setting.monitor.destroy');
            Route::post('lesson-plan/assignment', [\App\Http\Controllers\LessonPlanSettingController::class, 'storeAssignment'])->name('lesson-plan-setting.assignment.store');
            Route::delete('lesson-plan/assignment/{id}', [\App\Http\Controllers\LessonPlanSettingController::class, 'destroyAssignment'])->name('lesson-plan-setting.assignment.destroy');
            
            Route::get('lesson-plan-target', [\App\Http\Controllers\LessonPlanTargetController::class, 'index'])->name('lesson-plan-target.index');
            Route::post('lesson-plan-target', [\App\Http\Controllers\LessonPlanTargetController::class, 'store'])->name('lesson-plan-target.store');
            Route::delete('lesson-plan-target/{id}', [\App\Http\Controllers\LessonPlanTargetController::class, 'destroy'])->name('lesson-plan-target.destroy');
            // Assessment Settings
            Route::get('assessment', [\App\Http\Controllers\AssessmentSettingController::class, 'index'])->name('assessment-setting.index');
            Route::post('assessment/approver', [\App\Http\Controllers\AssessmentSettingController::class, 'storeApprover'])->name('assessment-setting.approver.store');
            Route::delete('assessment/approver/{id}', [\App\Http\Controllers\AssessmentSettingController::class, 'destroyApprover'])->name('assessment-setting.approver.destroy');
            Route::post('assessment/assignment', [\App\Http\Controllers\AssessmentSettingController::class, 'storeAssignment'])->name('assessment-setting.assignment.store');
            Route::delete('assessment/assignment/{id}', [\App\Http\Controllers\AssessmentSettingController::class, 'destroyAssignment'])->name('assessment-setting.assignment.destroy');
            
            Route::get('assessment-target', [\App\Http\Controllers\AssessmentTargetController::class, 'index'])->name('assessment-target.index');
            Route::post('assessment-target', [\App\Http\Controllers\AssessmentTargetController::class, 'store'])->name('assessment-target.store');
            Route::delete('assessment-target/{id}', [\App\Http\Controllers\AssessmentTargetController::class, 'destroy'])->name('assessment-target.destroy');
        });

        Route::group(['prefix' => 'profile'], function () {
            Route::get('personal/{id}', [EmployeeController::class, 'personal']);
            Route::put('personal', [PersonalController::class, 'update']);

            Route::get('employment/{id}', [EmployeeController::class, 'employment']);
            Route::put('employment', [EmploymentController::class, 'update']);

            Route::get('education/{id}', [EmployeeController::class, 'education']);
            Route::get('document/{id}', [EmployeeController::class, 'document']);
            Route::delete('document/{id}', [EmployeeController::class, 'deleteDocument']);
            Route::get('portofolio/{id}', [EmployeeController::class, 'portofolio']);
            Route::get('payrol-info/{id}', [EmployeeController::class, 'payrol_info']);
            Route::get('attendance/{id}', [EmployeeController::class, 'attendance']);
            Route::get('timeoff/{id}', [EmployeeController::class, 'timeoff']);
            Route::post('family', [EmployeeController::class, 'postFamily']);
            Route::delete('family/{id}', [EmployeeController::class, 'deleteFamily']);

            Route::post('emergency', [EmployeeController::class, 'postEcon']);
            Route::delete('emergency/{id}', [EmployeeController::class, 'deleteEcon']);

            Route::get('kpi/{id}', [EmployeeKpiController::class, 'index'])->name('employee.kpi.index');
            Route::post('kpi/{id}', [EmployeeKpiController::class, 'store'])->name('employee.kpi.store');
            Route::get('kpi/edit/{kpi_id}', [EmployeeKpiController::class, 'edit'])->name('employee.kpi.edit');
            Route::put('kpi/{kpi_id}', [EmployeeKpiController::class, 'update'])->name('employee.kpi.update');
            Route::delete('kpi/{kpi_id}', [EmployeeKpiController::class, 'destroy'])->name('employee.kpi.destroy');
            Route::get('kpi/{kpi_id}/calculate', [EmployeeKpiController::class, 'calculate'])->name('employee.kpi.calculate');
            Route::post('kpi/{kpi_id}/save-score', [EmployeeKpiController::class, 'saveScore'])->name('employee.kpi.save-score');
        });
        Route::group(['prefix' => 'time'], function () {
            Route::get('attendance', [AttendanceController::class, 'attendance']);
            Route::get('request/datatable', [ApprovalRequestController::class, 'dataTable']);
            Route::get('request/{id}/history', [ApprovalRequestController::class, 'history']);
            Route::get('request/{id}/approver', [ApprovalRequestController::class, 'approver']);
            Route::resource('request', ApprovalRequestController::class);
        });

        Route::group(['prefix' => 'lesson-plan'], function () {
            Route::get('my', [\App\Http\Controllers\EmployeeLessonPlanController::class, 'index'])->name('employee.lesson-plan.index');
            Route::get('my/target/{targetId}/assignment/{assignmentId}', [\App\Http\Controllers\EmployeeLessonPlanController::class, 'showTarget'])->name('employee.lesson-plan.submit-form');
            Route::post('my/month/{monthId}/assignment/{assignmentId}/week/{week}', [\App\Http\Controllers\EmployeeLessonPlanController::class, 'submit'])->name('employee.lesson-plan.submit');
            
            Route::get('approvals', [\App\Http\Controllers\LessonPlanApprovalController::class, 'index'])->name('lesson-plan.approvals.index');
            Route::post('approvals/{id}', [\App\Http\Controllers\LessonPlanApprovalController::class, 'process'])->name('lesson-plan.approvals.process');

            Route::get('monitoring', [\App\Http\Controllers\LessonPlanMonitoringController::class, 'index'])->name('employee.lesson-plan.monitoring.index');
            Route::get('monitoring/target/{id}', [\App\Http\Controllers\LessonPlanMonitoringController::class, 'showTarget'])->name('employee.lesson-plan.monitoring.show');
            Route::get('monitoring/target/{id}/subject/{subject_id}', [\App\Http\Controllers\LessonPlanMonitoringController::class, 'showSubject'])->name('employee.lesson-plan.monitoring.subject');
        });

        Route::group(['prefix' => 'assessment'], function () {
            Route::get('my', [\App\Http\Controllers\EmployeeAssessmentController::class, 'index'])->name('employee.assessment.index');
            Route::get('my/target/{targetId}/assignment/{assignmentId}', [\App\Http\Controllers\EmployeeAssessmentController::class, 'showTarget'])->name('employee.assessment.submit-form');
            Route::post('my/target/{targetId}/assignment/{assignmentId}', [\App\Http\Controllers\EmployeeAssessmentController::class, 'submit'])->name('employee.assessment.submit');
            
            Route::get('approvals', [\App\Http\Controllers\AssessmentApprovalController::class, 'index'])->name('assessment.approvals.index');
            Route::post('approvals/{id}', [\App\Http\Controllers\AssessmentApprovalController::class, 'process'])->name('assessment.approvals.process');
        });

        Route::resource('signature', SignatureController::class);
        Route::resource('internal-document', InternalDocumentController::class);
    });
    
    Route::get('storage/{path}', function ($path) {
        $file = storage_path('app/public/' . $path);

        if (!file_exists($file)) {
            abort(404);
        }

        $mime = mime_content_type($file);

        return response()->file($file, [
            'Content-Type' => $mime,
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization',
        ]);
    })->where('path', '.*');
});

Route::get('/ping', function () {
    return "OK";
});
