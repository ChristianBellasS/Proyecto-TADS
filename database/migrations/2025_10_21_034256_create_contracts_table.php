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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id(); // PK
            $table->unsignedBigInteger('employee_id');
            $table->string('contract_type', 100);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('salary', 10, 2);
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('department_id');
            $table->integer('vacation_days_per_year');
            $table->integer('probation_period_months');
            $table->boolean('is_active');
            $table->text('termination_reason')->nullable();
            $table->timestamps();

            // Claves foráneas
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('position_id')->references('id')->on('employeetype')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
