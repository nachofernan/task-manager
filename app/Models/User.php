<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación con el departamento al que pertenece el usuario
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relación con las tareas asignadas al usuario
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_user_id');
    }

    /**
     * Relación con las tareas creadas por el usuario
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Obtener todas las tareas que el usuario puede ver
     */
    public function getVisibleTasks()
    {
        return Task::where(function ($query) {
            // Tareas asignadas al usuario
            $query->where('assigned_user_id', $this->id)
                  // O tareas de su departamento
                  ->orWhere('department_id', $this->department_id)
                  // O tareas sin departamento (públicas)
                  ->orWhereNull('department_id');
        })->orWhere(function ($query) {
            // Tareas de departamentos subordinados (si el usuario es superior jerárquico)
            if ($this->department) {
                $subordinateDepartments = $this->department->descendants()->pluck('id');
                if ($subordinateDepartments->isNotEmpty()) {
                    $query->whereIn('department_id', $subordinateDepartments);
                }
            }
        });
    }

    /**
     * Relación con los comentarios creados por el usuario
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
