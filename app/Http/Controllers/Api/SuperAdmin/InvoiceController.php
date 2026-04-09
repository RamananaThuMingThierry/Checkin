<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\GenerateInvoiceRequest;
use App\Services\ActivityLogService;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly ActivityLogService $activityLogService,
    ) {
    }

    public function generate(int $subscription, GenerateInvoiceRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $invoice = $this->invoiceService->generateFromSubscription($subscription, $data);

            $this->activityLogService->createActivityLog([
                'user_id' => null,
                'action' => 'generated_invoice',
                'entity_type' => 'invoice',
                'entity_id' => $invoice->id,
                'message' => 'Generated invoice with ID: '.$invoice->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.invoices.generate',
                'status_code' => 201,
                'metadata' => [
                    'subscription_id' => $invoice->subscription_id,
                    'tenant_id' => $invoice->tenant_id,
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $invoice,
                'message' => 'Invoice generated successfully.',
                'success' => true,
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
