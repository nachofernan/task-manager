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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            
            // Contenido del comentario
            $table->text('content');
            
            // Usuario que creó el comentario
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Relación polimórfica para comentarios en tareas (futuro: otros modelos)
            $table->morphs('commentable');
            
            // Sistema de respuestas anidadas
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            
            // Estado del comentario
            $table->enum('status', ['active', 'hidden', 'deleted'])->default('active');
            
            // Timestamps
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['parent_id']);
            $table->index(['user_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
