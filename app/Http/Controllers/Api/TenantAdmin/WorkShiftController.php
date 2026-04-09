<?php

namespace App\Http\Controllers\Api\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantAdmin\StoreWorkShiftRequest;
use App\Services\WorkShiftService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkShiftController extends Controller
{
    public function __construct(private readonly WorkShiftService $workShiftService)
    {
    }

    public function store(StoreWorkShiftRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $workShift = $this->workShiftService->createWorkShift($data);

            DB::commit();

            return response()->json([
                'data' => $workShift,
                'message' => 'Work shift created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
