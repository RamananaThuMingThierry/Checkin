<?php

namespace App\Providers;

use App\Interfaces\ActivityLogInterface;
use App\Interfaces\AttendanceLogInterface;
use App\Interfaces\AttendanceRecordInterface;
use App\Interfaces\BranchInterface;
use App\Interfaces\DepartmentInterface;
use App\Interfaces\DeviceInterface;
use App\Interfaces\EmployeeInterface;
use App\Interfaces\EmployeeShiftAssignmentInterface;
use App\Interfaces\InvoiceInterface;
use App\Interfaces\LeaveTypeInterface;
use App\Interfaces\ModuleInterface;
use App\Interfaces\OfferInterface;
use App\Interfaces\PaymentInterface;
use App\Interfaces\PermissionInterface;
use App\Interfaces\RoleInterface;
use App\Interfaces\SubscriptionInterface;
use App\Interfaces\TenantInterface;
use App\Interfaces\TenantModuleInterface;
use App\Interfaces\UserInterface;
use App\Interfaces\WorkShiftInterface;
use App\Repositories\ActivityLogRepository;
use App\Repositories\AttendanceLogRepository;
use App\Repositories\AttendanceRecordRepository;
use App\Repositories\BranchRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\DeviceRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\EmployeeShiftAssignmentRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\OfferRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\SubscriptionRepository;
use App\Repositories\TenantModuleRepository;
use App\Repositories\TenantRepository;
use App\Repositories\UserRepository;
use App\Repositories\WorkShiftRepository;
use App\Testing\Database\TestingMySqlConnection;
use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ActivityLogInterface::class, ActivityLogRepository::class);
        $this->app->bind(AttendanceLogInterface::class, AttendanceLogRepository::class);
        $this->app->bind(AttendanceRecordInterface::class, AttendanceRecordRepository::class);
        $this->app->bind(BranchInterface::class, BranchRepository::class);
        $this->app->bind(DepartmentInterface::class, DepartmentRepository::class);
        $this->app->bind(DeviceInterface::class, DeviceRepository::class);
        $this->app->bind(EmployeeInterface::class, EmployeeRepository::class);
        $this->app->bind(EmployeeShiftAssignmentInterface::class, EmployeeShiftAssignmentRepository::class);
        $this->app->bind(InvoiceInterface::class, InvoiceRepository::class);
        $this->app->bind(LeaveTypeInterface::class, LeaveTypeRepository::class);
        $this->app->bind(ModuleInterface::class, ModuleRepository::class);
        $this->app->bind(OfferInterface::class, OfferRepository::class);
        $this->app->bind(PaymentInterface::class, PaymentRepository::class);
        $this->app->bind(PermissionInterface::class, PermissionRepository::class);
        $this->app->bind(RoleInterface::class, RoleRepository::class);
        $this->app->bind(SubscriptionInterface::class, SubscriptionRepository::class);
        $this->app->bind(TenantInterface::class, TenantRepository::class);
        $this->app->bind(TenantModuleInterface::class, TenantModuleRepository::class);
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(WorkShiftInterface::class, WorkShiftRepository::class);
    }

    public function boot(): void
    {
        if ($this->app->environment('testing')) {
            Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
                return new TestingMySqlConnection($connection, $database, $prefix, $config);
            });
        }
    }
}
