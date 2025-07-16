<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Events\Deleting;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'due_date',
        'priority',
        'status',
        'assigned_user_id',
        'department_id',
        'created_by',
        'completed_at'
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        // Eliminar comentarios cuando se elimina la tarea
        static::deleting(function ($task) {
            $task->comments()->delete();
        });
    }

    // Constantes para prioridades
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Constantes para estados
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_BLOCKED = 'blocked';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Relación con el usuario asignado
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Relación con el departamento
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relación con el usuario que creó la tarea
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con los comentarios de la tarea
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Comentarios principales (sin respuestas)
     */
    public function mainComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
                    ->whereNull('parent_id')
                    ->with(['user', 'replies.user', 'replies.replies.user']);
    }

    /**
     * Verificar si la tarea está vencida
     */
    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * Verificar si la tarea está próxima a vencer (3 días antes)
     */
    public function isDueSoon(): bool
    {
        return $this->due_date->diffInDays(now()) <= 3 && 
               !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * Verificar si un usuario puede ver esta tarea
     */
    public function canBeViewedBy(User $user): bool
    {
        // Si la tarea está asignada a un usuario específico, solo ese usuario puede verla o un superior
        if ($this->assigned_user_id != null) {
            return $this->assigned_user_id == $user->id || ($this->department && $user->department && $this->isUserHierarchicalSuperior($user));
        }

        // Si la tarea está asignada a un departamento, cualquier usuario de ese departamento o superior puede verla
        if ($this->department_id != null) {
            if ($user->department_id == $this->department_id) {
                return true;
            }
            // Verificar si el usuario es superior jerárquico
            if ($this->department && $user->department && $this->isUserHierarchicalSuperior($user)) {
                return true;
            }
            return false;
        }

        // Si la tarea no tiene departamento ni usuario asignado, cualquiera puede verla
        return true;
    }

    /**
     * Verificar si un usuario puede editar esta tarea
     */
    public function canBeEditedBy(User $user): bool
    {
        // El creador siempre puede editar
        if ($this->created_by === $user->id) {
            return true;
        }

        // Si está asignada a un usuario específico, solo ese usuario puede editar
        if ($this->assigned_user_id && $this->assigned_user_id === $user->id) {
            return true;
        }

        // Si está asignada a un departamento, verificar si el usuario pertenece a ese departamento
        if ($this->department_id && $user->department_id === $this->department_id) {
            return true;
        }

        // Verificar si el usuario es superior jerárquico
        if ($this->department && $user->department) {
            return $this->isUserHierarchicalSuperior($user);
        }

        return false;
    }

    /**
     * Verificar si un usuario es superior jerárquico del departamento de la tarea
     */
    private function isUserHierarchicalSuperior(User $user): bool
    {
        if (!$this->department || !$user->department) {
            return false;
        }

        // Si el usuario pertenece al mismo departamento, no es superior
        if ($user->department_id === $this->department_id) {
            return false;
        }

        // Verificar si el departamento del usuario es ancestro del departamento de la tarea
        $taskDepartmentAncestors = $this->department->ancestors();
        
        return $taskDepartmentAncestors->contains('id', $user->department_id);
    }

    /**
     * Marcar la tarea como completada
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now()
        ]);
    }

    /**
     * Marcar la tarea como no completada
     */
    public function markAsIncomplete(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'completed_at' => null
        ]);
    }

    /**
     * Scope para tareas vencidas
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * Scope para tareas próximas a vencer
     */
    public function scopeDueSoon($query)
    {
        return $query->where('due_date', '<=', now()->addDays(3))
                    ->where('due_date', '>=', now())
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * Scope para tareas por prioridad
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope para tareas por estado
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para tareas asignadas a un usuario
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_user_id', $userId);
    }

    /**
     * Scope para tareas de un departamento
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Obtener el color de la prioridad para la UI
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'green',
            self::PRIORITY_MEDIUM => 'yellow',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_URGENT => 'red',
            default => 'gray'
        };
    }

    /**
     * Obtener el color del estado para la UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'gray',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_BLOCKED => 'red',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_CANCELLED => 'gray',
            default => 'gray'
        };
    }
}
