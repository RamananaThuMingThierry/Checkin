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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
           $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();

            $table->string('badge_uid')->nullable();
            $table->enum('scan_type', ['in', 'out', 'break_start', 'break_end']);
            $table->timestamp('scanned_at');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('result', ['success', 'failed', 'duplicate', 'unauthorized'])->default('success');
            $table->string('message')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'scanned_at']);
            $table->index(['tenant_id', 'employee_id', 'scanned_at']);
            $table->index(['tenant_id', 'badge_uid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
