<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\SuperAdmin\BranchController as SuperAdminBranchController;
use App\Http\Controllers\Api\SuperAdmin\InvoiceController as SuperAdminInvoiceController;
use App\Http\Controllers\Api\SuperAdmin\ModuleController as SuperAdminModuleController;
use App\Http\Controllers\Api\SuperAdmin\OfferController as SuperAdminOfferController;
use App\Http\Controllers\Api\SuperAdmin\PaymentController as SuperAdminPaymentController;
use App\Http\Controllers\Api\SuperAdmin\PermissionController as SuperAdminPermissionController;
use App\Http\Controllers\Api\SuperAdmin\RoleController as SuperAdminRoleController;
use App\Http\Controllers\Api\SuperAdmin\SubscriptionController as SuperAdminSubscriptionController;
use App\Http\Controllers\Api\SuperAdmin\SuperAdminController;
use App\Http\Controllers\Api\SuperAdmin\TenantAdminController as SuperAdminTenantAdminController;
use App\Http\Controllers\Api\SuperAdmin\TenantController as SuperAdminTenantController;
use App\Http\Controllers\Api\SuperAdmin\TenantModuleController as SuperAdminTenantModuleController;
use App\Http\Controllers\Api\TenantAdmin\AttendanceLogController as TenantAdminAttendanceLogController;
use App\Http\Controllers\Api\TenantAdmin\DepartmentController as TenantAdminDepartmentController;
use App\Http\Controllers\Api\TenantAdmin\DeviceController as TenantAdminDeviceController;
use App\Http\Controllers\Api\TenantAdmin\EmployeeController as TenantAdminEmployeeController;
use App\Http\Controllers\Api\TenantAdmin\EmployeeShiftAssignmentController as TenantAdminEmployeeShiftAssignmentController;
use App\Http\Controllers\Api\TenantAdmin\HolidayController as TenantAdminHolidayController;
use App\Http\Controllers\Api\TenantAdmin\LeaveRequestController as TenantAdminLeaveRequestController;
use App\Http\Controllers\Api\TenantAdmin\LeaveTypeController as TenantAdminLeaveTypeController;
use App\Http\Controllers\Api\TenantAdmin\SettingController as TenantAdminSettingController;
use App\Http\Controllers\Api\TenantAdmin\WorkShiftController as TenantAdminWorkShiftController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/ping', function () {
        return response()->json([
            'message' => 'pong',
        ]);
    });

    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
        Route::middleware('api.token')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
        });
    });

    Route::prefix('super-admin')->group(function (): void {
        Route::post('/users', [SuperAdminController::class, 'store'])->name('api.superadmin.users.store');
        Route::post('/roles', [SuperAdminRoleController::class, 'store'])->name('api.superadmin.roles.store');
        Route::post('/permissions', [SuperAdminPermissionController::class, 'store'])->name('api.superadmin.permissions.store');
        Route::post('/offers', [SuperAdminOfferController::class, 'store'])->name('api.superadmin.offers.store');
        Route::get('/modules', [SuperAdminModuleController::class, 'index'])->name('api.superadmin.modules.index');
        Route::post('/modules', [SuperAdminModuleController::class, 'store'])->name('api.superadmin.modules.store');
        Route::post('/subscriptions', [SuperAdminSubscriptionController::class, 'store'])->name('api.superadmin.subscriptions.store');
        Route::post('/subscriptions/{subscription}/invoices', [SuperAdminInvoiceController::class, 'generate'])->name('api.superadmin.invoices.generate');
        Route::post('/subscriptions/{subscription}/activate-modules', [SuperAdminTenantModuleController::class, 'activateFromSubscription'])->name('api.superadmin.tenant_modules.activate');
        Route::get('/tenants/{tenant}/invoices', [SuperAdminInvoiceController::class, 'index'])->name('api.superadmin.invoices.index');
        Route::get('/tenants/{tenant}/modules', [SuperAdminTenantModuleController::class, 'index'])->name('api.superadmin.tenant_modules.index');
        Route::post('/invoices/{invoice}/payments', [SuperAdminPaymentController::class, 'store'])->name('api.superadmin.payments.store');
        Route::post('/roles/{role}/assign', [SuperAdminRoleController::class, 'assign'])->name('api.superadmin.roles.assign');
        Route::post('/permissions/{permission}/assign-role', [SuperAdminPermissionController::class, 'assignToRole'])->name('api.superadmin.permissions.assign_role');
        Route::post('/offers/{offer}/modules', [SuperAdminOfferController::class, 'attachModule'])->name('api.superadmin.offers.attach_module');
    });

    Route::post('/tenants', [SuperAdminTenantController::class, 'store']);
    Route::post('/branches/main', [SuperAdminBranchController::class, 'storeMain'])->name('api.superadmin.branches.store_main');
    Route::post('/tenant-admin/users', [SuperAdminTenantAdminController::class, 'store'])->name('api.superadmin.tenant_admins.store');
    Route::get('/tenants/{tenant}/departments', [TenantAdminDepartmentController::class, 'index'])->name('api.tenant_admin.departments.index');
    Route::get('/tenants/{tenant}/leave-types', [TenantAdminLeaveTypeController::class, 'index'])->name('api.tenant_admin.leave_types.index');
    Route::get('/tenants/{tenant}/holidays', [TenantAdminHolidayController::class, 'index'])->name('api.tenant_admin.holidays.index');
    Route::get('/tenants/{tenant}/settings', [TenantAdminSettingController::class, 'show'])->name('api.tenant_admin.settings.show');
    Route::get('/tenants/{tenant}/attendance-logs', [TenantAdminAttendanceLogController::class, 'index'])->name('api.tenant_admin.attendance_logs.index');
    Route::get('/tenants/{tenant}/attendance-records', [TenantAdminAttendanceLogController::class, 'listRecords'])->name('api.tenant_admin.attendance_records.index');
    Route::get('/tenants/{tenant}/attendance-report', [TenantAdminAttendanceLogController::class, 'listReport'])->name('api.tenant_admin.attendance_report.index');
    Route::get('/tenants/{tenant}/attendance-report/export', [TenantAdminAttendanceLogController::class, 'exportReport'])->name('api.tenant_admin.attendance_report.export');
    Route::get('/tenants/{tenant}/attendance-anomalies', [TenantAdminAttendanceLogController::class, 'listAnomalies'])->name('api.tenant_admin.attendance_logs.anomalies');
    Route::post('/tenants/{tenant}/attendance-logs/consolidate', [TenantAdminAttendanceLogController::class, 'consolidate'])->name('api.tenant_admin.attendance_logs.consolidate');
    Route::post('/departments', [TenantAdminDepartmentController::class, 'store'])->name('api.tenant_admin.departments.store');
    Route::post('/leave-types', [TenantAdminLeaveTypeController::class, 'store'])->name('api.tenant_admin.leave_types.store');
    Route::post('/holidays', [TenantAdminHolidayController::class, 'store'])->name('api.tenant_admin.holidays.store');
    Route::post('/leave-requests', [TenantAdminLeaveRequestController::class, 'store'])->name('api.tenant_admin.leave_requests.store');
    Route::put('/settings', [TenantAdminSettingController::class, 'update'])->name('api.tenant_admin.settings.update');
    Route::put('/departments/{department}', [TenantAdminDepartmentController::class, 'update'])->name('api.tenant_admin.departments.update');
    Route::post('/devices', [TenantAdminDeviceController::class, 'store'])->name('api.tenant_admin.devices.store');
    Route::put('/devices/{device}/branch', [TenantAdminDeviceController::class, 'assignBranch'])->name('api.tenant_admin.devices.assign_branch');
    Route::post('/attendance-logs', [TenantAdminAttendanceLogController::class, 'store'])->name('api.tenant_admin.attendance_logs.store');
    Route::post('/attendance-logs/{attendanceLog}/resolve-employee', [TenantAdminAttendanceLogController::class, 'resolveEmployee'])->name('api.tenant_admin.attendance_logs.resolve_employee');
    Route::post('/attendance-logs/{attendanceLog}/reject', [TenantAdminAttendanceLogController::class, 'reject'])->name('api.tenant_admin.attendance_logs.reject');
    Route::post('/employees', [TenantAdminEmployeeController::class, 'store'])->name('api.tenant_admin.employees.store');
    Route::post('/employee-shift-assignments', [TenantAdminEmployeeShiftAssignmentController::class, 'store'])->name('api.tenant_admin.employee_shift_assignments.store');
    Route::post('/work-shifts', [TenantAdminWorkShiftController::class, 'store'])->name('api.tenant_admin.work_shifts.store');
});
