<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Relaciones
            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->cascadeOnDelete();
            
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            // Estados y prioridades
            $table->enum('priority', ['baja', 'media', 'alta'])->default('media');
            $table->enum('status', [
                'pendiente', 
                'en_progreso', 
                'bloqueada', 
                'completada', 
                'cancelada'
            ])->default('pendiente');
            
            // Fechas importantes
            $table->datetime('due_date')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            
            // Metadata
            $table->boolean('is_archived')->default(false);
            $table->json('metadata')->nullable(); // Para futuras extensiones
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['department_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['due_date', 'status']);
            $table->index(['priority', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};