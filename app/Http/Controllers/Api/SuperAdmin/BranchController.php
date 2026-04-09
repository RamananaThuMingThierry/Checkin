<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreMainBranchRequest;
use App\Services\ActivityLogService;
use App\Services\BranchService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BranchController extends Controller
{
    public function __construct(
        private readonly BranchService $branchService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function storeMain(StoreMainBranchRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $branch = $this->branchService->createMainBranch($data);

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'created_main_branch',
                'entity_type' => 'branch',
                'entity_id' => $branch->id,
                'message' => 'Created main branch with ID: '.$branch->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.branches.store_main',
                'status_code' => 201,
                'metadata' => [
                    'tenant_id' => $branch->tenant_id,
                    'code' => $branch->code,
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $branch,
                'message' => 'Main branch created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'failed_main_branch_creation',
                'entity_type' => 'branch',
                'entity_id' => null,
                'message' => 'Failed to create main branch.',
                'color' => 'danger',
                'method' => 'POST',
                'route' => 'api.superadmin.branches.store_main',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => $data,
                ],
            ]);

            return response()->json([
                'message' => 'Failed to create main branch.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }
}
