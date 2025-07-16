<?php

namespace Tests\Feature\TaskManager;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class TaskModelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function puede_crear_una_tarea()
    {
        $task = Task::factory()->create();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'name' => $task->name,
            'description' => $task->description,
        ]);
    }

    /** @test */
    public function puede_crear_tarea_con_diferentes_estados()
    {
        $pendingTask = Task::factory()->pending()->create();
        $completedTask = Task::factory()->completed()->create();
        $urgentTask = Task::factory()->urgent()->create();

        $this->assertEquals('pending', $pendingTask->status);
        $this->assertEquals('completed', $completedTask->status);
        $this->assertEquals('urgent', $urgentTask->priority);
        $this->assertNotNull($completedTask->completed_at);
    }

    /** @test */
    public function puede_detectar_tareas_vencidas()
    {
        $overdueTask = Task::factory()->overdue()->create();
        $normalTask = Task::factory()->create();

        $this->assertTrue($overdueTask->isOverdue());
        $this->assertFalse($normalTask->isOverdue());
    }

    /** @test */
    public function puede_detectar_tareas_proximas_a_vencer()
    {
        $dueSoonTask = Task::factory()->create([
            'due_date' => now()->addDays(2),
            'status' => 'pending',
        ]);
        $farFutureTask = Task::factory()->create([
            'due_date' => now()->addMonths(2),
        ]);

        $this->assertTrue($dueSoonTask->isDueSoon());
        $this->assertFalse($farFutureTask->isDueSoon());
    }

    /** @test */
    public function puede_marcar_tarea_como_completada()
    {
        $task = Task::factory()->pending()->create();

        $task->markAsCompleted();

        $this->assertEquals('completed', $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);
    }

    /** @test */
    public function puede_marcar_tarea_como_no_completada()
    {
        $task = Task::factory()->completed()->create();

        $task->markAsIncomplete();

        $this->assertEquals('pending', $task->fresh()->status);
        $this->assertNull($task->fresh()->completed_at);
    }

    /** @test */
    public function tiene_relaciones_con_usuarios_y_departamentos()
    {
        $task = Task::factory()->create();

        $this->assertInstanceOf(User::class, $task->assignedUser);
        $this->assertInstanceOf(User::class, $task->creator);
        $this->assertInstanceOf(Department::class, $task->department);
    }

    /** @test */
    public function puede_tener_comentarios()
    {
        $task = Task::factory()->create();
        $comment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);

        $this->assertTrue($task->comments->contains($comment));
        $this->assertEquals(1, $task->comments->count());
    }

    /** @test */
    public function puede_obtener_solo_comentarios_principales()
    {
        $task = Task::factory()->create();
        
        // Comentario principal
        $mainComment = Comment::factory()->main()->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);

        // Respuesta al comentario
        $reply = Comment::factory()->reply($mainComment)->create();

        $mainComments = $task->mainComments;

        $this->assertTrue($mainComments->contains($mainComment));
        $this->assertFalse($mainComments->contains($reply));
        $this->assertEquals(1, $mainComments->count());
    }

    /** @test */
    public function puede_filtrar_tareas_por_estado()
    {
        Task::factory()->pending()->count(3)->create();
        Task::factory()->completed()->count(2)->create();

        $pendingTasks = Task::byStatus('pending')->get();
        $completedTasks = Task::byStatus('completed')->get();

        $this->assertEquals(3, $pendingTasks->count());
        $this->assertEquals(2, $completedTasks->count());
    }

    /** @test */
    public function puede_filtrar_tareas_por_prioridad()
    {
        Task::factory()->count(3)->create(['priority' => 'high']);
        Task::factory()->count(2)->create(['priority' => 'low']);

        $highPriorityTasks = Task::byPriority('high')->get();
        $lowPriorityTasks = Task::byPriority('low')->get();

        $this->assertEquals(3, $highPriorityTasks->count());
        $this->assertEquals(2, $lowPriorityTasks->count());
    }

    /** @test */
    public function puede_filtrar_tareas_vencidas_con_scope()
    {
        Task::factory()->overdue()->count(3)->create();
        Task::factory()->pending()->count(2)->create();

        $overdueTasks = Task::overdue()->get();

        $this->assertEquals(3, $overdueTasks->count());
    }

    /** @test */
    public function puede_filtrar_tareas_proximas_a_vencer_con_scope()
    {
        Task::factory()->dueSoon()->count(3)->create();
        Task::factory()->pending()->count(2)->create();

        $dueSoonTasks = Task::dueSoon()->get();

        $this->assertEquals(3, $dueSoonTasks->count());
    }

    /** @test */
    public function puede_filtrar_tareas_asignadas_a_usuario()
    {
        $user = User::factory()->create();
        Task::factory()->assignedTo($user->id)->count(3)->create();
        Task::factory()->count(2)->create();

        $assignedTasks = Task::assignedTo($user->id)->get();

        $this->assertEquals(3, $assignedTasks->count());
    }

    /** @test */
    public function puede_filtrar_tareas_por_departamento()
    {
        $department = Department::factory()->create();
        Task::factory()->byDepartment($department->id)->count(3)->create();
        Task::factory()->count(2)->create();

        $departmentTasks = Task::byDepartment($department->id)->get();

        $this->assertEquals(3, $departmentTasks->count());
    }

    /** @test */
    public function tiene_colores_de_prioridad()
    {
        $urgentTask = Task::factory()->urgent()->create();
        $lowTask = Task::factory()->create(['priority' => 'low']);

        $this->assertEquals('red', $urgentTask->priority_color);
        $this->assertEquals('green', $lowTask->priority_color);
    }

    /** @test */
    public function tiene_colores_de_estado()
    {
        $pendingTask = Task::factory()->pending()->create();
        $completedTask = Task::factory()->completed()->create();

        $this->assertEquals('gray', $pendingTask->status_color);
        $this->assertEquals('green', $completedTask->status_color);
    }
} 