<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'commentable_type',
        'commentable_id',
        'parent_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Usuario que creó el comentario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación polimórfica - el modelo que tiene el comentario
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Comentario padre (para respuestas)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Respuestas directas a este comentario
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Todas las respuestas anidadas (recursivo)
     */
    public function allReplies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('allReplies');
    }

    /**
     * Scope para comentarios activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para comentarios principales (sin padre)
     */
    public function scopeMainComments($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope para respuestas (con padre)
     */
    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Verificar si es una respuesta
     */
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Verificar si tiene respuestas
     */
    public function hasReplies(): bool
    {
        return $this->replies()->exists();
    }

    /**
     * Obtener el comentario raíz (el primer comentario del hilo)
     */
    public function getRootComment(): self
    {
        $comment = $this;
        while ($comment->parent) {
            $comment = $comment->parent;
        }
        return $comment;
    }

    /**
     * Obtener la profundidad del comentario en el hilo
     */
    public function getDepth(): int
    {
        $depth = 0;
        $comment = $this;
        while ($comment->parent) {
            $depth++;
            $comment = $comment->parent;
        }
        return $depth;
    }
}
