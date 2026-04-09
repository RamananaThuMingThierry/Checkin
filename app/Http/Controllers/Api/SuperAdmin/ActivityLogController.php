<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;

class ActivityLogController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService){}

    public function index()
    {
        $constraints = [];

        $activityLogs = $this->activityLogService->getAllActivityLogs(
            keys: array_keys($constraints),
            value: array_values($constraints),
            relations: ['user'],
            orderBy: ['created_at' => 'desc']
        );

        return response()->json([
            'data' => $activityLogs,
            'success' => true,
        ], 200);
    }

    public function show(string $encryptedId)
    {
        $id = decrypt_to_int_or_null($encryptedId);

        if(is_null($id)) {
            return response()->json([
                'message' => 'Invalid activity log ID',
                'success' => false,
            ], 400);
        }

        $activityLog = $this->activityLogService->getActivityLogById($id, relations: ['user']);

        if (!$activityLog) {
            return response()->json([
                'message' => 'Activity log not found',
                'success' => false,
            ], 404);
        }

        return response()->json([
            'data' => $activityLog,
            'success' => true,
        ], 200);
    }

    public function destroy(string $encryptedId)
    {
        $id = decrypt_to_int_or_null($encryptedId);

        if(is_null($id)) {
            return response()->json([
                'message' => 'Invalid activity log ID',
                'success' => false,
            ], 400);
        }

        try {
            $this->activityLogService->deleteActivityLog($id);

            return response()->json([
                'message' => 'Activity log deleted successfully',
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    public function restore(string $encryptedId)
    {
        $id = decrypt_to_int_or_null($encryptedId);

        if(is_null($id)) {
            return response()->json([
                'message' => 'Invalid activity log ID',
                'success' => false,
            ], 400);
        }

        try {
            $this->activityLogService->restoreActivityLog($id);

            return response()->json([
                'message' => 'Activity log restored successfully',
                'success' => true,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

        public function forceDelete(string $encryptedId)
        {
            $id = decrypt_to_int_or_null($encryptedId);

            if(is_null($id)) {
                return response()->json([
                    'message' => 'Invalid activity log ID',
                    'success' => false,
                ], 400);
            }

            try {
                $this->activityLogService->forceDeleteActivityLog($id);

                return response()->json([
                    'message' => 'Activity log permanently deleted successfully',
                    'success' => true,
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'success' => false,
                ], 500);
            }
        }
}
