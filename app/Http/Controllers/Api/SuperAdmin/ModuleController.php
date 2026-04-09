<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreModuleRequest;
use App\Services\ActivityLogService;
use App\Services\ModuleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ModuleController extends Controller
{
    public function __construct(
        private readonly ModuleService $moduleService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function index()
    {
        $modules = $this->moduleService->listModules();

        return response()->json([
            'data' => $modules,
            'success' => true,
        ], 200);
    }

    public function store(StoreModuleRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $module = $this->moduleService->createModule($data);

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'created_module',
                'entity_type' => 'module',
                'entity_id' => $module->id,
                'message' => 'Created module with ID: '.$module->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.modules.store',
                'status_code' => 201,
                'metadata' => [
                    'code' => $module->code,
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $module,
                'message' => 'Module created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
