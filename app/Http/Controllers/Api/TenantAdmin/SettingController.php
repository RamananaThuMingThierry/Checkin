<?php

namespace App\Http\Controllers\Api\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantAdmin\UpdateTenantSettingsRequest;
use App\Services\SettingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settingService)
    {
    }

    public function show(string $tenant)
    {
        $settings = $this->settingService->getTenantSettings($tenant);

        return response()->json([
            'data' => $settings,
            'success' => true,
        ], 200);
    }

    public function update(UpdateTenantSettingsRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $settings = $this->settingService->updateTenantSettings($data);
            DB::commit();

            return response()->json([
                'data' => $settings,
                'message' => 'Tenant settings updated successfully.',
                'success' => true,
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
