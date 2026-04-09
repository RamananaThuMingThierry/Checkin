<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreSubscriptionRequest;
use App\Services\ActivityLogService;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function store(StoreSubscriptionRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $subscription = $this->subscriptionService->createSubscription($data);

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'created_subscription',
                'entity_type' => 'subscription',
                'entity_id' => $subscription->id,
                'message' => 'Created subscription with ID: '.$subscription->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.subscriptions.store',
                'status_code' => 201,
                'metadata' => [
                    'tenant_id' => $subscription->tenant_id,
                    'offer_id' => $subscription->offer_id,
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $subscription,
                'message' => 'Subscription created successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
