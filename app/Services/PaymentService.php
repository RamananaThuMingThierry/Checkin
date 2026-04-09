<?php

namespace App\Services;

use App\Interfaces\InvoiceInterface;
use App\Interfaces\PaymentInterface;
use App\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly PaymentInterface $paymentRepository,
        private readonly InvoiceInterface $invoiceRepository,
    ) {
    }

    public function recordInvoicePayment(int $invoiceId, array $data): Payment
    {
        $invoice = $this->invoiceRepository->getById($invoiceId);

        if ($invoice === null) {
            throw ValidationException::withMessages([
                'invoice_id' => 'The selected invoice is invalid.',
            ]);
        }

        if ($invoice->currency !== $data['currency']) {
            throw ValidationException::withMessages([
                'currency' => 'The payment currency must match the invoice currency.',
            ]);
        }

        if ((float) $data['amount'] > (float) $invoice->balance_due) {
            throw ValidationException::withMessages([
                'amount' => 'The payment amount cannot exceed the remaining balance.',
            ]);
        }

        $payload = Arr::only($data, ['amount', 'currency', 'payment_date', 'reference', 'transaction_id', 'notes']);
        $paymentDate = isset($payload['payment_date']) ? Carbon::parse($payload['payment_date']) : Carbon::today();
        $newPaidAmount = (float) $invoice->paid_amount + (float) $payload['amount'];
        $newBalance = (float) $invoice->total - $newPaidAmount;
        $invoiceStatus = $newBalance <= 0 ? 'paid' : 'partially_paid';

        $payment = $this->paymentRepository->create([
            'tenant_id' => $invoice->tenant_id,
            'invoice_id' => $invoice->id,
            'payment_number' => $this->generatePaymentNumber(),
            'payment_date' => $paymentDate->toDateString(),
            'amount' => $payload['amount'],
            'currency' => $payload['currency'],
            'status' => 'successful',
            'reference' => $payload['reference'] ?? null,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'notes' => $payload['notes'] ?? null,
            'confirmed_at' => Carbon::now(),
        ]);

        $this->invoiceRepository->update($invoice, [
            'status' => $invoiceStatus,
            'paid_amount' => $newPaidAmount,
            'balance_due' => max($newBalance, 0),
            'paid_at' => $invoiceStatus === 'paid' ? Carbon::now() : null,
        ]);

        return $payment->load('invoice');
    }

    private function generatePaymentNumber(): string
    {
        return 'PAY-'.Str::upper(Str::random(10));
    }
}
