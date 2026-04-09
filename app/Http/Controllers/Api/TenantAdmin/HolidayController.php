<?php

namespace App\Http\Controllers\Api\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantAdmin\StoreHolidayRequest;
use App\Services\HolidayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HolidayController extends Controller
{
    public function __construct(private readonly HolidayService $holidayService)
    {
    }

    public function index(string $tenant)
    {
        $holidays = $this->holidayService->listHolidays($tenant);

        return response()->json([
            'data' => $holidays,
            'success' => true,
        ], 200);
    }

    public function store(StoreHolidayRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $holiday = $this->holidayService->createHoliday($data);
            DB::commit();

            return response()->json([
                'data' => $holiday,
                'message' => 'Holiday created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
