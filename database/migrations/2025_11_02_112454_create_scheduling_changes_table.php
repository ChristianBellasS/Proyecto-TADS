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
        Schema::create('scheduling_changes', function (Blueprint $table) {
            $table->id();

            // Relación con la programación que se modifica
            $table->foreignId('scheduling_id')
                ->constrained('schedulings')
                ->onDelete('cascade');

            $table->foreignId('changed_by')
                ->constrained('employees')
                ->onDelete('cascade');

            // Tipo de cambio (turno, vehículo u ocupante)
            $table->enum('change_type', ['turno', 'vehiculo', 'ocupante', 'conductor']);

            // Motivo del cambio (obligatorio)
            $table->text('reason');

            // Valores anteriores en formato JSON
            $table->json('old_values')->nullable();

            // Valores nuevos en formato JSON
            $table->json('new_values')->nullable();

            $table->timestamps();

            // Índices para mejor performance
            $table->index('scheduling_id');
            $table->index('change_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduling_changes');
    }
};
