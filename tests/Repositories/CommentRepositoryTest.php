<?php

namespace ITRvB\UnitTests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use ITRvB\Models\UUID;
use ITRvB\Models\Article;
use ITRvB\Models\Comment;
use ITRvB\Models\User;
use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Repositories\CommentRepositoryInterface;
use ITRvB\Repositories\ArticleRepositoryInterface;
use ITRvB\Exceptions\NotFoundException;
use Exception;

class CommentRepositoryTest extends TestCase
{
    private static MySQL $mysql;
    private static CommentRepositoryInterface $repo;
    private static Article $sampleArticle;
    private static User $sampleUser;

    public static function setUpBeforeClass() : void
    {
        self::$mysql = new MySQL();
        self::$repo = new CommentRepositoryInterface(self::$mysql);

        $articleRepository = new ArticleRepositoryInterface(self::$mysql);
        self::$sampleArticle = $articleRepository->get(new UUID("1b42fe64-a369-47f7-a269-360a3f785e5c")); // make sure it exists
        self::$sampleUser = self::$mysql->getUser(new UUID("1e54e6a0-6844-45a9-934d-9ec512ec7fc8")); // make sure it exists (required for article reposistory test aswell)
    }

    public static function tearDownAfterClass() : void
    {
        self::$mysql->dispose();
    }

    public function testSaveCommentWithUnexistingArticle() : void
    {
        $article = new Article(
            UUID::random(),
            User::createRandom(),
            "sample header",
            "sample text"
        );
        $comment = new Comment(
            UUID::random(),
            self::$sampleUser,
            $article,
            "sample text"
        );
        $this->expectException(Exception::class);
        self::$repo->save($comment);
    }

    public function testSaveCommentWithUnexistingUser() : void
    {
        $comment = new Comment(
            UUID::random(),
            User::createRandom(),
            self::$sampleArticle,
            "sample text"
        );
        $this->expectException(Exception::class);
        self::$repo->save($comment);
    }

    public function testGetUnexistingComment() : void
    {
        // Make sure DB does not have comment with given UUID.
        $fakeUuid = new UUID("4a712205-d463-4b90-bc65-be98bb577b2e");

        $this->expectException(NotFoundException::class);
        self::$repo->get($fakeUuid);
    }

    #[DoesNotPerformAssertions]
    public function testSaveComment() : Comment
    {
        $comment = new Comment(
            UUID::random(),
            self::$sampleUser,
            self::$sampleArticle,
            "sample text"
        );
        self::$repo->save($comment);
        return $comment;
    }

    #[Depends('testSaveComment')]
    public function testGetComment(Comment $comment) : Comment
    {
        $dbComment = self::$repo->get($comment->id);
        $this->assertEquals($comment, $dbComment);
        return $comment;
    }

    #[Depends('testGetComment')]
    public function testGetCommentByArticle(Comment $comment) : Comment
    {
        $articleComments = self::$repo->getByArticle($comment->article);
        $this->assertContainsEquals($comment, $articleComments);
        return $comment;
    }

    #[Depends('testGetCommentByArticle')]
    #[DoesNotPerformAssertions]
    public function testDeleteComment(Comment $comment) : void
    {
        self::$repo->delete($comment->id);
    }
}