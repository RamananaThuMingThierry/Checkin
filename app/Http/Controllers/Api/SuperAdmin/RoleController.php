<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\AssignGlobalRoleRequest;
use App\Http\Requests\SuperAdmin\StoreGlobalRoleRequest;
use App\Services\ActivityLogService;
use App\Services\RoleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function store(StoreGlobalRoleRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $role = $this->roleService->createGlobalRole($data);

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'created_global_role',
                'entity_type' => 'role',
                'entity_id' => $role->id,
                'message' => 'Created global role with ID: '.$role->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.roles.store',
                'status_code' => 201,
                'metadata' => [
                    'code' => $role->code,
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $role,
                'message' => 'Global role created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'failed_global_role_creation',
                'entity_type' => 'role',
                'entity_id' => null,
                'message' => 'Failed to create global role.',
                'color' => 'danger',
                'method' => 'POST',
                'route' => 'api.superadmin.roles.store',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => $data,
                ],
            ]);

            return response()->json([
                'message' => 'Failed to create global role.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    public function assign(AssignGlobalRoleRequest $request, int $role)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $assignedRole = $this->roleService->assignGlobalRoleToUser($role, (int) $data['user_id']);

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'assigned_global_role',
                'entity_type' => 'role',
                'entity_id' => $assignedRole->id,
                'message' => 'Assigned global role with ID: '.$assignedRole->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.roles.assign',
                'status_code' => 200,
                'metadata' => [
                    'user_id' => (int) $data['user_id'],
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $assignedRole,
                'message' => 'Global role assigned successfully.',
                'success' => true,
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'failed_global_role_assignment',
                'entity_type' => 'role',
                'entity_id' => $role,
                'message' => 'Failed to assign global role.',
                'color' => 'danger',
                'method' => 'POST',
                'route' => 'api.superadmin.roles.assign',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => $data,
                ],
            ]);

            return response()->json([
                'message' => 'Failed to assign global role.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }
}
