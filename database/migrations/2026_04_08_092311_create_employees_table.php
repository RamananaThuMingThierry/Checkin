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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();

            $table->string('employee_code');
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('position')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('badge_uid')->nullable();
            $table->string('photo')->nullable();

            $table->enum('status', ['active', 'inactive', 'suspended', 'resigned'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'employee_code']);
            $table->index(['tenant_id', 'branch_id', 'status']);
            $table->index(['tenant_id', 'department_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
