<?php

namespace Tests\Feature\TaskManager;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class TaskCommentIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_can_create_task_with_comments()
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
    public function it_can_create_complex_comment_thread()
    {
        $task = Task::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Comentario principal
        $mainComment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
            'user_id' => $user1->id,
        ]);

        // Respuestas al comentario principal
        $reply1 = Comment::factory()->reply($mainComment)->create([
            'user_id' => $user2->id,
        ]);
        $reply2 = Comment::factory()->reply($mainComment)->create([
            'user_id' => $user1->id,
        ]);

        // Respuesta a una respuesta
        $nestedReply = Comment::factory()->reply($reply1)->create([
            'user_id' => $user1->id,
        ]);

        $task->refresh();

        $this->assertEquals(4, $task->comments->count());
        $this->assertEquals(1, $task->mainComments->count());
        $this->assertEquals(2, $mainComment->replies->count());
        $this->assertEquals(1, $reply1->replies->count());
    }

    /** @test */
    public function it_can_get_task_with_comments_and_replies()
    {
        $task = Task::factory()->create();
        $mainComment = Comment::factory()->withReplies(3)->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);

        $task->refresh();

        $this->assertEquals(4, $task->comments->count()); // 1 principal + 3 respuestas
        $this->assertEquals(3, $mainComment->replies->count());
    }

    /** @test */
    public function it_can_delete_task_and_cascade_comments()
    {
        $task = Task::factory()->create();
        $mainComment = Comment::factory()->withReplies(2)->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);

        $task->delete();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertDatabaseMissing('comments', ['commentable_id' => $task->id]);
    }

    /** @test */
    public function it_can_filter_comments_by_status()
    {
        $task = Task::factory()->create();
        
        Comment::factory()->active()->count(3)->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);
        
        Comment::factory()->hidden()->count(2)->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);

        $activeComments = $task->comments()->active()->get();
        $hiddenComments = $task->comments()->where('status', 'hidden')->get();

        $this->assertEquals(3, $activeComments->count());
        $this->assertEquals(2, $hiddenComments->count());
    }

    /** @test */
    public function it_can_get_comments_by_user()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        Comment::factory()->count(3)->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
            'user_id' => $user->id,
        ]);

        Comment::factory()->count(2)->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);

        $userComments = $task->comments()->where('user_id', $user->id)->get();

        $this->assertEquals(3, $userComments->count());
    }

    /** @test */
    public function it_can_get_task_with_eager_loaded_comments()
    {
        $task = Task::factory()->create();
        $mainComment = Comment::factory()->withReplies(2)->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);

        $taskWithComments = Task::with(['comments.user', 'comments.replies.user'])
            ->find($task->id);

        $this->assertTrue($taskWithComments->relationLoaded('comments'));
        $this->assertEquals(3, $taskWithComments->comments->count());
    }

    /** @test */
    public function it_can_create_task_with_multiple_comment_threads()
    {
        $task = Task::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Primer hilo de comentarios
        $thread1 = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
            'user_id' => $user1->id,
        ]);
        Comment::factory()->reply($thread1)->count(2)->create([
            'user_id' => $user2->id,
        ]);

        // Segundo hilo de comentarios
        $thread2 = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
            'user_id' => $user2->id,
        ]);
        Comment::factory()->reply($thread2)->count(1)->create([
            'user_id' => $user1->id,
        ]);

        $task->refresh();

        $this->assertEquals(5, $task->comments->count()); // 2 principales + 3 respuestas
        $this->assertEquals(2, $task->mainComments->count());
    }

    /** @test */
    public function it_can_get_comments_with_depth_information()
    {
        $task = Task::factory()->create();
        $mainComment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);
        
        $reply = Comment::factory()->reply($mainComment)->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);
        
        $nestedReply = Comment::factory()->reply($reply)->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);

        $this->assertEquals(0, $mainComment->getDepth());
        $this->assertEquals(1, $reply->getDepth());
        $this->assertEquals(2, $nestedReply->getDepth());
    }

    /** @test */
    public function it_can_get_root_comment_from_nested_reply()
    {
        $task = Task::factory()->create();
        $mainComment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);
        
        $reply = Comment::factory()->reply($mainComment)->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);
        
        $nestedReply = Comment::factory()->reply($reply)->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);

        $this->assertEquals($mainComment->id, $nestedReply->getRootComment()->id);
        $this->assertEquals($mainComment->id, $reply->getRootComment()->id);
    }

    /** @test */
    public function it_can_handle_complex_nested_threads()
    {
        $task = Task::factory()->create();
        $mainComment = Comment::factory()->withNestedReplies(3, 2)->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);

        $task->refresh();

        // 1 principal + 2 respuestas directas + 4 respuestas a respuestas + 8 respuestas a respuestas de respuestas = 15
        $this->assertEquals(15, $task->comments->count());
        $this->assertEquals(2, $mainComment->allReplies->count()); // Solo respuestas directas
    }
} 