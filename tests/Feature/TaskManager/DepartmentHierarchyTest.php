<?php

use App\Models\User;
use App\Models\Department;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear estructura jerárquica de departamentos
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

    $this->programacion = Department::create([
        'name' => 'Programación',
        'description' => 'Subdepartamento de Programación',
        'code' => 'PRO',
        'is_active' => true,
        'parent_id' => $this->desarrollo->id,
        'level' => 1
    ]);

    // Crear usuarios en diferentes niveles jerárquicos
    $this->jefeDesarrollo = User::create([
        'name' => 'Carlos Rodríguez',
        'email' => 'carlos@test.com',
        'password' => bcrypt('password'),
        'department_id' => $this->desarrollo->id
    ]);

    $this->subjefeDiseno = User::create([
        'name' => 'Luis García',
        'email' => 'luis@test.com',
        'password' => bcrypt('password'),
        'department_id' => $this->diseno->id
    ]);

    $this->empleadoDiseno = User::create([
        'name' => 'Pedro Sánchez',
        'email' => 'pedro@test.com',
        'password' => bcrypt('password'),
        'department_id' => $this->diseno->id
    ]);

    $this->empleadoProgramacion = User::create([
        'name' => 'Sofia Herrera',
        'email' => 'sofia@test.com',
        'password' => bcrypt('password'),
        'department_id' => $this->programacion->id
    ]);
});

test('verifica jerarquía de departamentos', function () {
    // Verificar que Desarrollo es padre de Diseño y Programación
    expect($this->desarrollo->children)->toHaveCount(2)
        ->and($this->desarrollo->children->pluck('name'))->toContain('Diseño', 'Programación');

    // Verificar que Diseño y Programación tienen a Desarrollo como padre
    expect($this->diseno->parent->name)->toBe('Desarrollo')
        ->and($this->programacion->parent->name)->toBe('Desarrollo');

    // Verificar ancestros de Diseño
    expect($this->diseno->ancestors())->toHaveCount(1)
        ->and($this->diseno->ancestors()->first()->name)->toBe('Desarrollo');

    // Verificar descendientes de Desarrollo
    expect($this->desarrollo->descendants())->toHaveCount(2)
        ->and($this->desarrollo->descendants()->pluck('name'))->toContain('Diseño', 'Programación');
});

test('jefe puede ver tareas de subordinados', function () {
    // Crear tarea en el departamento de Diseño
    $taskDiseno = Task::create([
        'name' => 'Diseñar interfaz de usuario',
        'description' => 'Crear mockups para la nueva aplicación',
        'due_date' => now()->addDays(7),
        'priority' => Task::PRIORITY_HIGH,
        'status' => Task::STATUS_PENDING,
        'created_by' => $this->subjefeDiseno->id,
        'department_id' => $this->diseno->id
    ]);

    // Crear tarea en el departamento de Programación
    $taskProgramacion = Task::create([
        'name' => 'Implementar API REST',
        'description' => 'Desarrollar endpoints para la aplicación',
        'due_date' => now()->addDays(10),
        'priority' => Task::PRIORITY_MEDIUM,
        'status' => Task::STATUS_IN_PROGRESS,
        'created_by' => $this->empleadoProgramacion->id,
        'department_id' => $this->programacion->id
    ]);

    // Verificar que el jefe de Desarrollo puede ver ambas tareas
    expect($taskDiseno->canBeViewedBy($this->jefeDesarrollo))->toBeTrue()
        ->and($taskProgramacion->canBeViewedBy($this->jefeDesarrollo))->toBeTrue();

    // Verificar que el subjefe de Diseño puede ver su tarea pero no la de Programación
    expect($taskDiseno->canBeViewedBy($this->subjefeDiseno))->toBeTrue()
        ->and($taskProgramacion->canBeViewedBy($this->subjefeDiseno))->toBeFalse();

    // Verificar que el empleado de Diseño puede ver su tarea
    expect($taskDiseno->canBeViewedBy($this->empleadoDiseno))->toBeTrue();
});

test('jefe puede editar tareas de subordinados', function () {
    $task = Task::create([
        'name' => 'Optimizar base de datos',
        'description' => 'Mejorar el rendimiento de las consultas',
        'due_date' => now()->addDays(5),
        'priority' => Task::PRIORITY_HIGH,
        'status' => Task::STATUS_PENDING,
        'created_by' => $this->empleadoDiseno->id,
        'department_id' => $this->diseno->id
    ]);

    // Verificar que el jefe puede editar la tarea
    expect($task->canBeEditedBy($this->jefeDesarrollo))->toBeTrue();

    // Verificar que el subjefe puede editar la tarea
    expect($task->canBeEditedBy($this->subjefeDiseno))->toBeTrue();

    // Verificar que el empleado puede editar su propia tarea
    expect($task->canBeEditedBy($this->empleadoDiseno))->toBeTrue();
});

test('usuario puede ver tareas de su departamento y subordinados', function () {
    // Crear tareas en diferentes departamentos
    Task::create([
        'name' => 'Tarea en Diseño',
        'description' => 'Tarea del departamento de diseño',
        'due_date' => now()->addDays(3),
        'priority' => Task::PRIORITY_MEDIUM,
        'status' => Task::STATUS_PENDING,
        'created_by' => $this->subjefeDiseno->id,
        'department_id' => $this->diseno->id
    ]);

    Task::create([
        'name' => 'Tarea en Programación',
        'description' => 'Tarea del departamento de programación',
        'due_date' => now()->addDays(5),
        'priority' => Task::PRIORITY_LOW,
        'status' => Task::STATUS_PENDING,
        'created_by' => $this->empleadoProgramacion->id,
        'department_id' => $this->programacion->id
    ]);

    // Verificar que el jefe ve todas las tareas de sus subordinados
    $visibleTasksJefe = $this->jefeDesarrollo->getVisibleTasks();
    expect($visibleTasksJefe->count())->toBe(2);

    // Verificar que el subjefe solo ve las tareas de su departamento
    $visibleTasksSubjefe = $this->subjefeDiseno->getVisibleTasks();
    expect($visibleTasksSubjefe->count())->toBe(1);
});

test('verifica métodos de utilidad de departamentos', function () {
    // Verificar si es departamento raíz
    expect($this->desarrollo->isRoot())->toBeTrue()
        ->and($this->diseno->isRoot())->toBeFalse();

    // Verificar si es departamento hoja
    expect($this->desarrollo->isLeaf())->toBeFalse()
        ->and($this->diseno->isLeaf())->toBeTrue();

    // Verificar niveles
    expect($this->desarrollo->level)->toBe(0)
        ->and($this->diseno->level)->toBe(1);

    // Verificar ruta completa
    expect($this->diseno->full_path)->toBe('Desarrollo > Diseño');
}); 