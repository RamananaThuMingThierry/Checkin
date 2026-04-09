<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreTenantAdminRequest;
use App\Services\ActivityLogService;
use App\Services\TenantAdminService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TenantAdminController extends Controller
{
    public function __construct(
        private readonly TenantAdminService $tenantAdminService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function store(StoreTenantAdminRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $user = $this->tenantAdminService->createTenantAdmin($data);

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'created_tenant_admin',
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'message' => 'Created tenant admin with ID: '.$user->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.tenant_admins.store',
                'status_code' => 201,
                'metadata' => [
                    'tenant_id' => $user->tenant_id,
                    'email' => $user->email,
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $user,
                'message' => 'Tenant admin created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'failed_tenant_admin_creation',
                'entity_type' => 'user',
                'entity_id' => null,
                'message' => 'Failed to create tenant admin.',
                'color' => 'danger',
                'method' => 'POST',
                'route' => 'api.superadmin.tenant_admins.store',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => [
                        'tenant_id' => $data['tenant_id'] ?? null,
                        'email' => $data['email'] ?? null,
                    ],
                ],
            ]);

            return response()->json([
                'message' => 'Failed to create tenant admin.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }
}
