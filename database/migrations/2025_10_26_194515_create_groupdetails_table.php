<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groupdetails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('scheduling_id')->constrained('schedulings')->onDelete('cascade');
            $table->enum('role', ['conductor', 'ayudante'])->default('ayudante');
            $table->timestamps();

            // Evitar duplicados
            $table->unique(['employee_id', 'scheduling_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groupdetails');
    }
};
