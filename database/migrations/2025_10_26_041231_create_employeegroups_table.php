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
        Schema::create('employeegroups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('days', 255);
            $table->string('status', 100)->default('active');
            $table->unsignedBigInteger('zone_id');
            $table->unsignedBigInteger('shift_id');
            $table->unsignedBigInteger('vehicle_id');
            #Configuramos un Conductor
            $table->unsignedBigInteger('driver_id')->nullable();
            #Configuramos los Asistentes - se tomo como referencia max 5 asistentes por vehiculo para dar holgura
            $table->unsignedBigInteger('assistant1_id')->nullable();
            $table->unsignedBigInteger('assistant2_id')->nullable();
            $table->unsignedBigInteger('assistant3_id')->nullable();
            $table->unsignedBigInteger('assistant4_id')->nullable();
            $table->unsignedBigInteger('assistant5_id')->nullable();
            $table->timestamps();

            $table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('assistant1_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('assistant2_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('assistant3_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('assistant4_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('assistant5_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employeegroups');
    }
};