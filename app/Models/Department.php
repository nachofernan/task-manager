<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'code',
        'is_active',
        'level',
        'path'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer'
    ];

    /**
     * Relación con el departamento padre
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * Relación con los departamentos hijos
     */
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    /**
     * Relación con todos los usuarios del departamento
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relación con todas las tareas del departamento
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Obtener todos los ancestros del departamento
     */
    public function ancestors()
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors->reverse();
    }

    /**
     * Obtener todos los descendientes del departamento
     */
    public function descendants()
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }

    /**
     * Obtener la ruta completa del departamento
     */
    public function getFullPathAttribute(): string
    {
        $ancestors = $this->ancestors();
        $ancestors->push($this);
        
        return $ancestors->pluck('name')->implode(' > ');
    }

    /**
     * Verificar si es un departamento raíz
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Verificar si es un departamento hoja (sin hijos)
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    /**
     * Obtener el nivel en la jerarquía
     */
    public function getLevelAttribute(): int
    {
        if ($this->isRoot()) {
            return 0;
        }

        return $this->parent->level + 1;
    }

    /**
     * Scope para departamentos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para departamentos raíz
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope para departamentos con hijos
     */
    public function scopeWithChildren($query)
    {
        return $query->whereHas('children');
    }
}
