<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => fake()->paragraph(1, 3),
            'user_id' => User::factory(),
            'commentable_type' => Task::class,
            'commentable_id' => Task::factory(),
            'parent_id' => null,
            'status' => 'active',
        ];
    }

    /**
     * Comentario principal (sin padre)
     */
    public function main(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }

    /**
     * Respuesta a un comentario
     */
    public function reply(Comment $parentComment): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentComment->id,
            'commentable_type' => $parentComment->commentable_type,
            'commentable_id' => $parentComment->commentable_id,
        ]);
    }

    /**
     * Comentario activo
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Comentario oculto
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'hidden',
        ]);
    }

    /**
     * Comentario eliminado
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'deleted',
        ]);
    }

    /**
     * Comentario largo
     */
    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->paragraphs(3, 5),
        ]);
    }

    /**
     * Comentario corto
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->sentence(),
        ]);
    }

    /**
     * Crear un hilo de comentarios (comentario principal + respuestas)
     */
    public function withReplies(int $repliesCount = 3): static
    {
        return $this->afterCreating(function (Comment $comment) use ($repliesCount) {
            // Crear respuestas al comentario principal
            for ($i = 0; $i < $repliesCount; $i++) {
                Comment::factory()
                    ->reply($comment)
                    ->create();
            }
        });
    }

    /**
     * Crear un hilo de comentarios anidados (respuestas a respuestas)
     */
    public function withNestedReplies(int $levels = 2, int $repliesPerLevel = 2): static
    {
        return $this->afterCreating(function (Comment $comment) use ($levels, $repliesPerLevel) {
            $this->createNestedReplies($comment, $levels, $repliesPerLevel);
        });
    }

    /**
     * Método auxiliar para crear respuestas anidadas
     */
    private function createNestedReplies(Comment $parent, int $levels, int $repliesPerLevel): void
    {
        if ($levels <= 0) return;

        for ($i = 0; $i < $repliesPerLevel; $i++) {
            $reply = Comment::factory()
                ->reply($parent)
                ->create();

            $this->createNestedReplies($reply, $levels - 1, $repliesPerLevel);
        }
    }
}
