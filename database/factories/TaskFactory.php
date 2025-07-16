<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['pending', 'in_progress', 'blocked', 'completed', 'cancelled'];
        
        return [
            'name' => fake()->sentence(3, 6),
            'description' => fake()->paragraph(2, 4),
            'due_date' => fake()->dateTimeBetween('now', '+2 months'),
            'priority' => fake()->randomElement($priorities),
            'status' => fake()->randomElement($statuses),
            'assigned_user_id' => User::factory(),
            'department_id' => Department::factory(),
            'created_by' => User::factory(),
            'completed_at' => null,
        ];
    }

    /**
     * Tarea pendiente
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }

    /**
     * Tarea en progreso
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'completed_at' => null,
        ]);
    }

    /**
     * Tarea bloqueada
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'blocked',
            'completed_at' => null,
        ]);
    }

    /**
     * Tarea completada
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Tarea cancelada
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'completed_at' => null,
        ]);
    }

    /**
     * Tarea vencida
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-1 month', '-1 day'),
            'status' => fake()->randomElement(['pending', 'in_progress', 'blocked']),
        ]);
    }

    /**
     * Tarea próxima a vencer
     */
    public function dueSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('now', '+3 days'),
            'status' => fake()->randomElement(['pending', 'in_progress']),
        ]);
    }

    /**
     * Tarea de alta prioridad
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => fake()->randomElement(['high', 'urgent']),
        ]);
    }

    /**
     * Tarea de baja prioridad
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => fake()->randomElement(['low', 'medium']),
        ]);
    }

    /**
     * Tarea urgente
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
            'status' => fake()->randomElement(['pending', 'in_progress']),
        ]);
    }

    /**
     * Tarea sin asignar
     */
    public function unassigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_user_id' => null,
        ]);
    }

    /**
     * Tarea sin departamento
     */
    public function noDepartment(): static
    {
        return $this->state(fn (array $attributes) => [
            'department_id' => null,
        ]);
    }

    /**
     * Tarea asignada a un usuario específico
     */
    public function assignedTo(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_user_id' => $userId,
        ]);
    }

    /**
     * Tarea de un departamento específico
     */
    public function byDepartment(int $departmentId): static
    {
        return $this->state(fn (array $attributes) => [
            'department_id' => $departmentId,
        ]);
    }
}
