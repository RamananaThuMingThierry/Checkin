<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Services\TenantModuleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TenantModuleController extends Controller
{
    public function __construct(
        private readonly TenantModuleService $tenantModuleService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function index(int $tenant)
    {
        $tenantModules = $this->tenantModuleService->listActiveModules($tenant);

        return response()->json([
            'data' => $tenantModules,
            'success' => true,
        ], 200);
    }

    public function activateFromSubscription(int $subscription)
    {
        try {
            DB::beginTransaction();

            $tenantModules = $this->tenantModuleService->activateFromSubscription($subscription);

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'activated_tenant_modules',
                'entity_type' => 'subscription',
                'entity_id' => $subscription,
                'message' => 'Activated tenant modules from subscription ID: '.$subscription,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.tenant_modules.activate',
                'status_code' => 200,
                'metadata' => [
                    'activated_count' => $tenantModules->count(),
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $tenantModules,
                'message' => 'Tenant modules activated successfully.',
                'success' => true,
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
