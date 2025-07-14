<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')
                  ->constrained('tasks')
                  ->cascadeOnDelete();
            
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('comments')
                  ->cascadeOnDelete();
            
            $table->text('content');
            $table->boolean('is_internal')->default(false); // Para comentarios internos vs públicos
            $table->json('metadata')->nullable(); // Para futuras extensiones
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['task_id', 'created_at']);
            $table->index(['parent_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};