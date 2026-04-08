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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_shift_id')->nullable()->constrained()->nullOnDelete();

            $table->date('attendance_date');
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();

            $table->unsignedInteger('worked_minutes')->default(0);
            $table->unsignedInteger('break_minutes')->default(0);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);

            $table->enum('status', [
                'present',
                'late',
                'absent',
                'on_leave',
                'on_mission',
                'holiday',
                'incomplete'
            ])->default('absent');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'employee_id', 'attendance_date']);
            $table->index(['tenant_id', 'attendance_date']);
            $table->index(['tenant_id', 'status', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
