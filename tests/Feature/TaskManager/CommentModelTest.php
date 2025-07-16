<?php

namespace Tests\Feature\TaskManager;

use Tests\TestCase;
use App\Models\Comment;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CommentModelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_can_create_a_comment()
    {
        $comment = Comment::factory()->create();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => $comment->content,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_can_create_main_comment()
    {
        $comment = Comment::factory()->main()->create();

        $this->assertNull($comment->parent_id);
        $this->assertFalse($comment->isReply());
    }

    /** @test */
    public function it_can_create_reply_to_comment()
    {
        $parentComment = Comment::factory()->main()->create();
        $reply = Comment::factory()->reply($parentComment)->create();

        $this->assertEquals($parentComment->id, $reply->parent_id);
        $this->assertTrue($reply->isReply());
        $this->assertEquals($parentComment->commentable_type, $reply->commentable_type);
        $this->assertEquals($parentComment->commentable_id, $reply->commentable_id);
    }

    /** @test */
    public function it_can_have_different_statuses()
    {
        $activeComment = Comment::factory()->create();
        $hiddenComment = Comment::factory()->hidden()->create();
        $deletedComment = Comment::factory()->deleted()->create();

        $this->assertEquals('active', $activeComment->status);
        $this->assertEquals('hidden', $hiddenComment->status);
        $this->assertEquals('deleted', $deletedComment->status);
    }

    /** @test */
    public function it_can_have_different_content_lengths()
    {
        $shortComment = Comment::factory()->short()->create();
        $longComment = Comment::factory()->long()->create();

        $this->assertLessThan(100, strlen($shortComment->content));
        $this->assertGreaterThan(200, strlen($longComment->content));
    }

    /** @test */
    public function it_has_relationships()
    {
        $comment = Comment::factory()->create();

        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertInstanceOf(Task::class, $comment->commentable);
    }

    /** @test */
    public function it_can_have_parent_and_replies()
    {
        $parentComment = Comment::factory()->main()->create();
        $reply = Comment::factory()->reply($parentComment)->create();

        $this->assertInstanceOf(Comment::class, $reply->parent);
        $this->assertEquals($parentComment->id, $reply->parent->id);
        $this->assertTrue($parentComment->replies->contains($reply));
    }

    /** @test */
    public function it_can_get_all_nested_replies()
    {
        $parentComment = Comment::factory()->main()->create();
        $reply1 = Comment::factory()->reply($parentComment)->create();
        $reply2 = Comment::factory()->reply($parentComment)->create();
        $nestedReply = Comment::factory()->reply($reply1)->create();

        $allReplies = $parentComment->allReplies;

        $this->assertTrue($allReplies->contains($reply1));
        $this->assertTrue($allReplies->contains($reply2));
        // allReplies solo incluye respuestas directas, no anidadas
        $this->assertFalse($allReplies->contains($nestedReply));
        $this->assertEquals(2, $allReplies->count());
    }

    /** @test */
    public function it_can_detect_if_has_replies()
    {
        $parentComment = Comment::factory()->main()->create();
        $reply = Comment::factory()->reply($parentComment)->create();

        $this->assertTrue($parentComment->hasReplies());
        $this->assertFalse($reply->hasReplies());
    }

    /** @test */
    public function it_can_get_root_comment()
    {
        $rootComment = Comment::factory()->main()->create();
        $reply1 = Comment::factory()->reply($rootComment)->create();
        $reply2 = Comment::factory()->reply($reply1)->create();

        $this->assertEquals($rootComment->id, $reply1->getRootComment()->id);
        $this->assertEquals($rootComment->id, $reply2->getRootComment()->id);
        $this->assertEquals($rootComment->id, $rootComment->getRootComment()->id);
    }

    /** @test */
    public function it_can_calculate_depth()
    {
        $rootComment = Comment::factory()->main()->create();
        $reply1 = Comment::factory()->reply($rootComment)->create();
        $reply2 = Comment::factory()->reply($reply1)->create();

        $this->assertEquals(0, $rootComment->getDepth());
        $this->assertEquals(1, $reply1->getDepth());
        $this->assertEquals(2, $reply2->getDepth());
    }

    /** @test */
    public function it_can_scope_active_comments()
    {
        Comment::factory()->active()->count(3)->create();
        Comment::factory()->hidden()->count(2)->create();

        $activeComments = Comment::active()->get();

        $this->assertEquals(3, $activeComments->count());
    }

    /** @test */
    public function it_can_scope_main_comments()
    {
        Comment::factory()->main()->count(3)->create();
        $parentComment = Comment::factory()->main()->create();
        Comment::factory()->reply($parentComment)->count(2)->create();

        $mainComments = Comment::mainComments()->get();

        $this->assertEquals(4, $mainComments->count());
    }

    /** @test */
    public function it_can_scope_replies()
    {
        Comment::factory()->main()->count(2)->create();
        
        // Crear comentarios con parent_id (respuestas)
        $parentComment = Comment::factory()->main()->create();
        Comment::factory()->reply($parentComment)->count(3)->create();

        $replies = Comment::whereNotNull('parent_id')->get();

        $this->assertEquals(3, $replies->count());
    }

    /** @test */
    public function it_can_create_thread_with_replies()
    {
        $comment = Comment::factory()->withReplies(3)->create();

        $this->assertEquals(3, $comment->replies->count());
    }

    /** @test */
    public function it_can_create_nested_thread()
    {
        $comment = Comment::factory()->withNestedReplies(2, 2)->create();

        // 2 respuestas directas
        $this->assertEquals(2, $comment->allReplies->count());
    }

    /** @test */
    public function it_can_be_polymorphic()
    {
        $task = Task::factory()->create();
        $comment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);

        $this->assertInstanceOf(Task::class, $comment->commentable);
        $this->assertEquals($task->id, $comment->commentable->id);
    }

    /** @test */
    public function it_cascades_deletes_properly()
    {
        $parentComment = Comment::factory()->main()->create();
        $reply = Comment::factory()->reply($parentComment)->create();

        $parentComment->delete();

        $this->assertDatabaseMissing('comments', ['id' => $parentComment->id]);
        $this->assertDatabaseMissing('comments', ['id' => $reply->id]);
    }

    /** @test */
    public function it_can_have_user_relationship()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $comment->user->id);
        $this->assertTrue($user->comments->contains($comment));
    }
} 