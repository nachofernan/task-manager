<?php

use App\Models\User;
use App\Models\Department;
use App\Models\Task;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear permisos
    $permissions = [
        'view_tasks', 'create_tasks', 'edit_tasks', 'delete_tasks',
        'assign_tasks', 'complete_tasks', 'view_all_tasks',
        'manage_departments', 'view_reports', 'manage_users'
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    // Crear roles
    $roles = [
        'jefe' => [
            'view_tasks', 'create_tasks', 'edit_tasks', 'delete_tasks',
            'assign_tasks', 'complete_tasks', 'view_all_tasks',
            'manage_departments', 'view_reports', 'manage_users'
        ],
        'subjefe' => [
            'view_tasks', 'create_tasks', 'edit_tasks', 'assign_tasks',
            'complete_tasks', 'view_reports'
        ],
        'empleado' => [
            'view_tasks', 'create_tasks', 'edit_tasks', 'complete_tasks'
        ]
    ];

    foreach ($roles as $roleName => $rolePermissions) {
        $role = Role::create(['name' => $roleName]);
        $role->givePermissionTo($rolePermissions);
    }

    // Crear departamento
    $this->departamento = Department::create([
        'name' => 'Desarrollo',
        'description' => 'Departamento de Desarrollo',
        'code' => 'DES',
        'is_active' => true,
        'level' => 0
    ]);

    // Crear usuarios con diferentes roles
    $this->jefe = User::create([
        'name' => 'Carlos Rodríguez',
        'email' => 'carlos@test.com',
        'password' => bcrypt('password'),
        'department_id' => $this->departamento->id
    ]);
    $this->jefe->assignRole('jefe');

    $this->subjefe = User::create([
        'name' => 'Luis García',
        'email' => 'luis@test.com',
        'password' => bcrypt('password'),
        'department_id' => $this->departamento->id
    ]);
    $this->subjefe->assignRole('subjefe');

    $this->empleado = User::create([
        'name' => 'Pedro Sánchez',
        'email' => 'pedro@test.com',
        'password' => bcrypt('password'),
        'department_id' => $this->departamento->id
    ]);
    $this->empleado->assignRole('empleado');
});

test('verifica que los roles tienen los permisos correctos', function () {
    // Verificar permisos del jefe
    expect($this->jefe->hasPermissionTo('view_tasks'))->toBeTrue()
        ->and($this->jefe->hasPermissionTo('create_tasks'))->toBeTrue()
        ->and($this->jefe->hasPermissionTo('delete_tasks'))->toBeTrue()
        ->and($this->jefe->hasPermissionTo('manage_users'))->toBeTrue();

    // Verificar permisos del subjefe
    expect($this->subjefe->hasPermissionTo('view_tasks'))->toBeTrue()
        ->and($this->subjefe->hasPermissionTo('create_tasks'))->toBeTrue()
        ->and($this->subjefe->hasPermissionTo('delete_tasks'))->toBeFalse()
        ->and($this->subjefe->hasPermissionTo('manage_users'))->toBeFalse();

    // Verificar permisos del empleado
    expect($this->empleado->hasPermissionTo('view_tasks'))->toBeTrue()
        ->and($this->empleado->hasPermissionTo('create_tasks'))->toBeTrue()
        ->and($this->empleado->hasPermissionTo('delete_tasks'))->toBeFalse()
        ->and($this->empleado->hasPermissionTo('assign_tasks'))->toBeFalse();
});

test('verifica que los usuarios pueden realizar acciones según sus permisos', function () {
    // Crear una tarea
    $task = Task::create([
        'name' => 'Implementar nueva funcionalidad',
        'description' => 'Desarrollar nueva característica del sistema',
        'due_date' => now()->addDays(7),
        'priority' => Task::PRIORITY_HIGH,
        'status' => Task::STATUS_PENDING,
        'created_by' => $this->jefe->id,
        'department_id' => $this->departamento->id
    ]);

    // Verificar que todos pueden ver la tarea (tienen permiso view_tasks)
    expect($task->canBeViewedBy($this->jefe))->toBeTrue()
        ->and($task->canBeViewedBy($this->subjefe))->toBeTrue()
        ->and($task->canBeViewedBy($this->empleado))->toBeTrue();

    // Verificar que todos pueden editar la tarea (tienen permiso edit_tasks)
    expect($task->canBeEditedBy($this->jefe))->toBeTrue()
        ->and($task->canBeEditedBy($this->subjefe))->toBeTrue()
        ->and($task->canBeEditedBy($this->empleado))->toBeTrue();
});

test('verifica que los usuarios pueden completar tareas según sus permisos', function () {
    $task = Task::create([
        'name' => 'Revisar documentación',
        'description' => 'Revisar y actualizar la documentación',
        'due_date' => now()->addDays(3),
        'priority' => Task::PRIORITY_MEDIUM,
        'status' => Task::STATUS_IN_PROGRESS,
        'created_by' => $this->jefe->id,
        'assigned_user_id' => $this->empleado->id,
        'department_id' => $this->departamento->id
    ]);

    // Todos tienen permiso complete_tasks, así que pueden marcar como completada
    $task->markAsCompleted();

    expect($task->fresh()->status)->toBe(Task::STATUS_COMPLETED)
        ->and($task->fresh()->completed_at)->not->toBeNull();
});

test('verifica que los roles pueden asignar tareas según sus permisos', function () {
    // Solo jefe y subjefe tienen permiso assign_tasks
    expect($this->jefe->hasPermissionTo('assign_tasks'))->toBeTrue()
        ->and($this->subjefe->hasPermissionTo('assign_tasks'))->toBeTrue()
        ->and($this->empleado->hasPermissionTo('assign_tasks'))->toBeFalse();
});

test('verifica que solo el jefe puede eliminar tareas', function () {
    expect($this->jefe->hasPermissionTo('delete_tasks'))->toBeTrue()
        ->and($this->subjefe->hasPermissionTo('delete_tasks'))->toBeFalse()
        ->and($this->empleado->hasPermissionTo('delete_tasks'))->toBeFalse();
});

test('verifica que solo el jefe puede gestionar usuarios', function () {
    expect($this->jefe->hasPermissionTo('manage_users'))->toBeTrue()
        ->and($this->subjefe->hasPermissionTo('manage_users'))->toBeFalse()
        ->and($this->empleado->hasPermissionTo('manage_users'))->toBeFalse();
});

test('verifica que jefe y subjefe pueden ver reportes', function () {
    expect($this->jefe->hasPermissionTo('view_reports'))->toBeTrue()
        ->and($this->subjefe->hasPermissionTo('view_reports'))->toBeTrue()
        ->and($this->empleado->hasPermissionTo('view_reports'))->toBeFalse();
});

test('verifica que los usuarios pueden crear tareas según sus permisos', function () {
    // Todos tienen permiso create_tasks
    expect($this->jefe->hasPermissionTo('create_tasks'))->toBeTrue()
        ->and($this->subjefe->hasPermissionTo('create_tasks'))->toBeTrue()
        ->and($this->empleado->hasPermissionTo('create_tasks'))->toBeTrue();
});

test('verifica que los usuarios pueden editar tareas según sus permisos', function () {
    // Todos tienen permiso edit_tasks
    expect($this->jefe->hasPermissionTo('edit_tasks'))->toBeTrue()
        ->and($this->subjefe->hasPermissionTo('edit_tasks'))->toBeTrue()
        ->and($this->empleado->hasPermissionTo('edit_tasks'))->toBeTrue();
}); 