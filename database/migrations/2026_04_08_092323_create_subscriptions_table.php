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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
                        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained()->restrictOnDelete();

            $table->string('subscription_number')->unique();

            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semiannual', 'yearly'])->default('monthly');
            $table->enum('status', ['trial', 'active', 'past_due', 'unpaid', 'cancelled', 'expired', 'suspended'])->default('trial');

            $table->date('trial_ends_at')->nullable();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->date('next_billing_date')->nullable();
            $table->date('cancelled_at')->nullable();

            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 10)->default('MGA');

            $table->string('external_provider')->nullable();
            $table->string('external_subscription_id')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'next_billing_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
