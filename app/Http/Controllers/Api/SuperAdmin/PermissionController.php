<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\AssignPermissionToRoleRequest;
use App\Http\Requests\SuperAdmin\StorePermissionRequest;
use App\Services\ActivityLogService;
use App\Services\PermissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionService $permissionService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function store(StorePermissionRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $permission = $this->permissionService->createPermission($data);

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'created_permission',
                'entity_type' => 'permission',
                'entity_id' => $permission->id,
                'message' => 'Created permission with ID: '.$permission->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.permissions.store',
                'status_code' => 201,
                'metadata' => [
                    'code' => $permission->code,
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $permission,
                'message' => 'Permission created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'failed_permission_creation',
                'entity_type' => 'permission',
                'entity_id' => null,
                'message' => 'Failed to create permission.',
                'color' => 'danger',
                'method' => 'POST',
                'route' => 'api.superadmin.permissions.store',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => $data,
                ],
            ]);

            return response()->json([
                'message' => 'Failed to create permission.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    public function assignToRole(AssignPermissionToRoleRequest $request, int $permission)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $assignedPermission = $this->permissionService->assignPermissionToRole($permission, (int) $data['role_id']);

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'assigned_permission_to_role',
                'entity_type' => 'permission',
                'entity_id' => $assignedPermission->id,
                'message' => 'Assigned permission with ID: '.$assignedPermission->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.permissions.assign_role',
                'status_code' => 200,
                'metadata' => [
                    'role_id' => (int) $data['role_id'],
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $assignedPermission,
                'message' => 'Permission assigned to role successfully.',
                'success' => true,
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'failed_permission_assignment',
                'entity_type' => 'permission',
                'entity_id' => $permission,
                'message' => 'Failed to assign permission to role.',
                'color' => 'danger',
                'method' => 'POST',
                'route' => 'api.superadmin.permissions.assign_role',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => $data,
                ],
            ]);

            return response()->json([
                'message' => 'Failed to assign permission to role.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }
}
