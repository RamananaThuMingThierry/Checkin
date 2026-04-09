<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\AttachModuleToOfferRequest;
use App\Http\Requests\SuperAdmin\StoreOfferRequest;
use App\Models\Module;
use App\Services\ActivityLogService;
use App\Services\OfferService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfferController extends Controller
{
    public function __construct(
        private readonly OfferService $offerService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function store(StoreOfferRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $offer = $this->offerService->createOffer($data);

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'created_offer',
                'entity_type' => 'offer',
                'entity_id' => $offer->id,
                'message' => 'Created offer with ID: '.$offer->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.offers.store',
                'status_code' => 201,
                'metadata' => [
                    'code' => $offer->code,
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $offer,
                'message' => 'Offer created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'failed_offer_creation',
                'entity_type' => 'offer',
                'entity_id' => null,
                'message' => 'Failed to create offer.',
                'color' => 'danger',
                'method' => 'POST',
                'route' => 'api.superadmin.offers.store',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => $data,
                ],
            ]);

            return response()->json([
                'message' => 'Failed to create offer.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    public function attachModule(AttachModuleToOfferRequest $request, int $offer)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            if (Module::query()->whereKey((int) $data['module_id'])->doesntExist()) {
                throw ValidationException::withMessages([
                    'module_id' => 'The selected module is invalid.',
                ]);
            }

            $updatedOffer = $this->offerService->attachModuleToOffer(
                $offer,
                (int) $data['module_id'],
                (bool) ($data['is_included'] ?? true),
            );

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'attached_module_to_offer',
                'entity_type' => 'offer',
                'entity_id' => $updatedOffer->id,
                'message' => 'Attached module to offer with ID: '.$updatedOffer->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.offers.attach_module',
                'status_code' => 200,
                'metadata' => [
                    'module_id' => (int) $data['module_id'],
                    'is_included' => (bool) ($data['is_included'] ?? true),
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $updatedOffer,
                'message' => 'Module attached to offer successfully.',
                'success' => true,
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'failed_offer_module_attachment',
                'entity_type' => 'offer',
                'entity_id' => $offer,
                'message' => 'Failed to attach module to offer.',
                'color' => 'danger',
                'method' => 'POST',
                'route' => 'api.superadmin.offers.attach_module',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => $data,
                ],
            ]);

            return response()->json([
                'message' => 'Failed to attach module to offer.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }
}
