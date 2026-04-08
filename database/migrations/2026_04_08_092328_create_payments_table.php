<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();

            $table->string('payment_number')->unique();
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('MGA');

            $table->enum('status', ['pending', 'processing', 'successful', 'failed', 'cancelled', 'refunded'])->default('pending');

            $table->string('reference')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('external_provider')->nullable();
            $table->string('external_payment_id')->nullable();

            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'payment_date']);
            $table->index(['reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
