<?php

use App\Models\User;
use App\Models\Department;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear departamentos de prueba
    $this->desarrollo = Department::create([
        'name' => 'Desarrollo',
        'description' => 'Departamento de Desarrollo',
        'code' => 'DES',
        'is_active' => true,
        'level' => 0
    ]);

    $this->diseno = Department::create([
        'name' => 'Diseño',
        'description' => 'Subdepartamento de Diseño',
        'code' => 'DIS',
        'is_active' => true,
        'parent_id' => $this->desarrollo->id,
        'level' => 1
    ]);

    // Crear usuarios de prueba
    $this->jefe = User::create([
        'name' => 'Carlos Rodríguez',
        'email' => 'carlos@test.com',
        'password' => bcrypt('password'),
        'department_id' => $this->desarrollo->id
    ]);

    $this->empleado = User::create([
        'name' => 'Pedro Sánchez',
        'email' => 'pedro@test.com',
        'password' => bcrypt('password'),
        'department_id' => $this->diseno->id
    ]);

    $this->empleado2 = User::create([
        'name' => 'Carmen Ruiz',
        'email' => 'carmen@test.com',
        'password' => bcrypt('password'),
        'department_id' => $this->diseno->id
    ]);
});

test('puede crear una tarea básica', function () {
    $task = Task::create([
        'name' => 'Crear diseño de landing page',
        'description' => 'Diseñar una landing page moderna para el producto X',
        'due_date' => now()->addDays(7),
        'priority' => Task::PRIORITY_HIGH,
        'status' => Task::STATUS_PENDING,
        'created_by' => $this->jefe->id,
        'department_id' => $this->diseno->id
    ]);

    expect($task)->toBeInstanceOf(Task::class)
        ->and($task->name)->toBe('Crear diseño de landing page')
        ->and($task->priority)->toBe(Task::PRIORITY_HIGH)
        ->and($task->status)->toBe(Task::STATUS_PENDING)
        ->and($task->department_id)->toBe($this->diseno->id)
        ->and($task->created_by)->toBe($this->jefe->id);
});

test('puede asignar una tarea a un usuario específico', function () {
    $task = Task::create([
        'name' => 'Implementar funcionalidad de login',
        'description' => 'Crear el sistema de autenticación',
        'due_date' => now()->addDays(5),
        'priority' => Task::PRIORITY_MEDIUM,
        'status' => Task::STATUS_PENDING,
        'created_by' => $this->jefe->id,
        'assigned_user_id' => $this->empleado->id,
        'department_id' => $this->diseno->id
    ]);

    expect($task->assigned_user_id)->toBe($this->empleado->id)
        ->and($task->assignedUser)->toBeInstanceOf(User::class)
        ->and($task->assignedUser->name)->toBe('Pedro Sánchez');
});

test('puede marcar una tarea como completada', function () {
    $task = Task::create([
        'name' => 'Revisar documentación',
        'description' => 'Revisar y actualizar la documentación del proyecto',
        'due_date' => now()->addDays(3),
        'priority' => Task::PRIORITY_LOW,
        'status' => Task::STATUS_IN_PROGRESS,
        'created_by' => $this->jefe->id,
        'assigned_user_id' => $this->empleado->id,
        'department_id' => $this->diseno->id
    ]);

    $task->markAsCompleted();

    expect($task->fresh()->status)->toBe(Task::STATUS_COMPLETED)
        ->and($task->fresh()->completed_at)->not->toBeNull()
        ->and($task->fresh()->completed_at->format('Y-m-d'))->toBe(now()->format('Y-m-d'));
});

test('puede marcar una tarea como no completada', function () {
    $task = Task::create([
        'name' => 'Optimizar base de datos',
        'description' => 'Optimizar las consultas de la base de datos',
        'due_date' => now()->addDays(10),
        'priority' => Task::PRIORITY_HIGH,
        'status' => Task::STATUS_COMPLETED,
        'created_by' => $this->jefe->id,
        'assigned_user_id' => $this->empleado->id,
        'department_id' => $this->diseno->id,
        'completed_at' => now()
    ]);

    $task->markAsIncomplete();

    expect($task->fresh()->status)->toBe(Task::STATUS_PENDING)
        ->and($task->fresh()->completed_at)->toBeNull();
});

test('puede eliminar una tarea', function () {
    $task = Task::create([
        'name' => 'Tarea temporal',
        'description' => 'Esta tarea será eliminada',
        'due_date' => now()->addDays(1),
        'priority' => Task::PRIORITY_LOW,
        'status' => Task::STATUS_PENDING,
        'created_by' => $this->jefe->id,
        'department_id' => $this->diseno->id
    ]);

    $taskId = $task->id;
    $task->delete();

    expect(Task::find($taskId))->toBeNull();
});

test('verifica permisos de visualización de tareas', function () {
    // Tarea asignada a un usuario específico
    $taskAsignada = Task::create([
        'name' => 'Tarea asignada',
        'description' => 'Tarea asignada a Pedro',
        'due_date' => now()->addDays(5),
        'priority' => Task::PRIORITY_MEDIUM,
        'status' => Task::STATUS_PENDING,
        'created_by' => $this->jefe->id,
        'assigned_user_id' => $this->empleado->id,
        'department_id' => $this->diseno->id
    ]);

    // Tarea de departamento sin usuario asignado
    $taskDepartamento = Task::create([
        'name' => 'Tarea de departamento',
        'description' => 'Tarea del departamento de diseño',
        'due_date' => now()->addDays(3),
        'priority' => Task::PRIORITY_LOW,
        'status' => Task::STATUS_PENDING,
        'created_by' => $this->jefe->id,
        'department_id' => $this->diseno->id
    ]);



    // Verificar que el empleado asignado puede ver su tarea
    expect($taskAsignada->canBeViewedBy($this->empleado))->toBeTrue();

    // Verificar que otro empleado del mismo departamento puede ver la tarea del departamento
    expect($taskDepartamento->canBeViewedBy($this->empleado2))->toBeTrue();

    // Verificar que el jefe puede ver ambas tareas (es superior jerárquico)
    expect($taskAsignada->canBeViewedBy($this->jefe))->toBeTrue()
        ->and($taskDepartamento->canBeViewedBy($this->jefe))->toBeTrue();
});

test('verifica scopes de filtrado de tareas', function () {
    // Crear tareas con diferentes estados y prioridades
    Task::create([
        'name' => 'Tarea urgente',
        'description' => 'Tarea de alta prioridad',
        'due_date' => now()->addDays(1),
        'priority' => Task::PRIORITY_URGENT,
        'status' => Task::STATUS_PENDING,
        'created_by' => $this->jefe->id,
        'department_id' => $this->diseno->id
    ]);

    Task::create([
        'name' => 'Tarea completada',
        'description' => 'Tarea ya finalizada',
        'due_date' => now()->subDays(1),
        'priority' => Task::PRIORITY_MEDIUM,
        'status' => Task::STATUS_COMPLETED,
        'created_by' => $this->jefe->id,
        'department_id' => $this->diseno->id,
        'completed_at' => now()
    ]);

    Task::create([
        'name' => 'Tarea vencida',
        'description' => 'Tarea que ya venció',
        'due_date' => now()->subDays(5),
        'priority' => Task::PRIORITY_HIGH,
        'status' => Task::STATUS_IN_PROGRESS,
        'created_by' => $this->jefe->id,
        'department_id' => $this->diseno->id
    ]);

    // Verificar scopes
    expect(Task::byPriority(Task::PRIORITY_URGENT)->count())->toBe(1)
        ->and(Task::byStatus(Task::STATUS_COMPLETED)->count())->toBe(1)
        ->and(Task::overdue()->count())->toBe(1)
        ->and(Task::byDepartment($this->diseno->id)->count())->toBe(3);
}); 