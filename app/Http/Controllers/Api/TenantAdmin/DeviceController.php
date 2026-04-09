<?php

namespace App\Http\Controllers\Api\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantAdmin\AssignDeviceToBranchRequest;
use App\Http\Requests\TenantAdmin\StoreDeviceRequest;
use App\Services\DeviceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeviceController extends Controller
{
    public function __construct(private readonly DeviceService $deviceService)
    {
    }

    public function store(StoreDeviceRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $device = $this->deviceService->createDevice($data);

            DB::commit();

            return response()->json([
                'data' => $device,
                'message' => 'Device registered successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function assignBranch(AssignDeviceToBranchRequest $request, int $device)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $updatedDevice = $this->deviceService->assignBranch($device, (int) $data['branch_id']);

            DB::commit();

            return response()->json([
                'data' => $updatedDevice,
                'message' => 'Device assigned to branch successfully.',
                'success' => true,
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
