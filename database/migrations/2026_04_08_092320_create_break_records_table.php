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
        Schema::create('break_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_record_id')->constrained()->cascadeOnDelete();

            $table->timestamp('break_start')->nullable();
            $table->timestamp('break_end')->nullable();
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->string('break_type')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'attendance_record_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('break_records');
    }
};
