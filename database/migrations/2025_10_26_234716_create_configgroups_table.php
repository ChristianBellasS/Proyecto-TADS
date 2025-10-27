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
        Schema::create('configgroups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employeegroup_id')->constrained('employeegroups')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('role')->default('ayudante'); // conductor, ayudante
            $table->timestamps();

            // Índice único para evitar duplicados
            $table->unique(['employeegroup_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configgroups');
    }
};