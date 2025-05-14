<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proyecto', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->foreignId('semillero_id')->nullable()->constrained('semillero');
            $table->foreignId('lider_id')->nullable()->constrained('usuario');
            $table->foreignId('coordinador_id')->nullable()->constrained('usuario');
            $table->enum('estado', ['activo', 'inactivo', 'completado', 'cancelado'])->default('activo');
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyecto');
    }
};
