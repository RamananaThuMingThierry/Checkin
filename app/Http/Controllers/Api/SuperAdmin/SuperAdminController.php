<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreSuperAdminRequest;
use App\Services\ActivityLogService;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SuperAdminController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function show(int $encryptedId)
    {
        try{
            $id = decrypt_to_int_or_null($encryptedId);

            if (is_null($id)) {
                return response()->json([
                    'message' => 'Invalid super-admin ID.',
                    'success' => false,
                ], 400);
            }

            $user = $this->userService->getSuperAdminById($id);

            if (!$user) {
                return response()->json([
                    'message' => 'Super-admin not found.',
                    'success' => false,
                ], 404);
            }

            return response()->json([
                'data' => $user,
                'message' => 'Super-admin retrieved successfully.',
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid super-admin ID.',
                'success' => false,
            ], 400);
        }
    }

    public function store(StoreSuperAdminRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $user = $this->userService->createSuperAdmin($data);

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'created_super_admin',
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'message' => 'Created super-admin with ID: '.$user->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.users.store',
                'status_code' => 201,
                'metadata' => [
                    'email' => $user->email,
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $user,
                'message' => 'Super-admin created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'failed_super_admin_creation',
                'entity_type' => 'user',
                'entity_id' => null,
                'message' => 'Failed to create super-admin.',
                'color' => 'danger',
                'method' => 'POST',
                'route' => 'api.superadmin.users.store',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => [
                        'name' => $data['name'] ?? null,
                        'email' => $data['email'] ?? null,
                    ],
                ],
            ]);

            return response()->json([
                'message' => 'Failed to create super-admin.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }
}
