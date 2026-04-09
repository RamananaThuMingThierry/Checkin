<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StorePaymentRequest;
use App\Services\ActivityLogService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function store(int $invoice, StorePaymentRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $payment = $this->paymentService->recordInvoicePayment($invoice, $data);

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'recorded_payment',
                'entity_type' => 'payment',
                'entity_id' => $payment->id,
                'message' => 'Recorded payment with ID: '.$payment->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.payments.store',
                'status_code' => 201,
                'metadata' => [
                    'invoice_id' => $payment->invoice_id,
                    'tenant_id' => $payment->tenant_id,
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $payment,
                'message' => 'Payment recorded successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
