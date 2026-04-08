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
        Schema::create('work_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('break_duration_minutes')->default(0);
            $table->unsignedInteger('late_tolerance_minutes')->default(0);
            $table->boolean('is_night_shift')->default(false);

            $table->timestamps();

            $table->index(['tenant_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_shifts');
    }
};
