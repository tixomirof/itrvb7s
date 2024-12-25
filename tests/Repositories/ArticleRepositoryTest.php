<?php

namespace ITRvB\UnitTests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use ITRvB\Models\UUID;
use ITRvB\Models\Article;
use ITRvB\Models\User;
use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Repositories\ArticleRepositoryInterface;
use ITRvB\Exceptions\NotFoundException;
use Exception;

class ArticleRepositoryTest extends TestCase
{
    private static MySQL $mysql;
    private static ArticleRepositoryInterface $repo;

    public static function setUpBeforeClass() : void
    {
        self::$mysql = new MySQL();
        self::$repo = new ArticleRepositoryInterface(self::$mysql);
    }

    public static function tearDownAfterClass() : void
    {
        self::$mysql->dispose();
    }

    public function testSaveArticleWithUnexistingUser() : void
    {
        $article = new Article(
            UUID::random(),
            User::createRandom(),
            "sample header",
            "sample text"
        );
        $this->expectException(Exception::class);
        self::$repo->save($article);
    }

    public function testGetUnexistingArticle() : void
    {
        // Make sure DB does not have article with UUID given below.
        $fakeUuid = new UUID("4a712205-d463-4b90-bc65-be98bb577b2e");

        $this->expectException(NotFoundException::class);
        self::$repo->get($fakeUuid);
    }

    #[DoesNotPerformAssertions]
    public function testSaveArticle() : Article
    {
        // Must create user with UUID 1e54e6a0-6844-45a9-934d-9ec512ec7fc8 before testing.
        $user = self::$mysql->getUser(new UUID("1e54e6a0-6844-45a9-934d-9ec512ec7fc8"));

        $article = new Article(
            UUID::random(),
            $user,
            "sample header",
            "sample text"
        );
        self::$repo->save($article);
        return $article;
    }

    #[Depends('testSaveArticle')]
    public function testGetArticle(Article $article) : Article
    {
        $dbArticle = self::$repo->get($article->id);
        $this->assertEquals($article, $dbArticle);
        return $article;
    }

    #[Depends('testGetArticle')]
    #[DoesNotPerformAssertions]
    public function testDeleteArticle(Article $article) : void
    {
        self::$repo->delete($article->id);
    }
}