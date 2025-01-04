<?php

namespace ITRvB\UnitTests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ITRvB\Models\UUID;
use ITRvB\Models\Article;
use ITRvB\Models\ArticleLike;
use ITRvB\Models\User;
use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Repositories\LikeRepositoryInterface;
use ITRvB\Repositories\ArticleRepositoryInterface;
use ITRvB\Exceptions\NotFoundException;
use ITRvB\Exceptions\RepetitiveLikeException;
use Exception;

class LikeRepositoryTest extends TestCase
{
    private static MySQL $mysql;
    private static LikeRepositoryInterface $repo;
    private static Article $sampleArticle;
    private static User $sampleUser;

    public static function setUpBeforeClass() : void
    {
        self::$mysql = new MySQL();
        self::$repo = new LikeRepositoryInterface(self::$mysql);

        $articleRepository = new ArticleRepositoryInterface(self::$mysql);
        self::$sampleUser = self::$mysql->getUser(new UUID("1e54e6a0-6844-45a9-934d-9ec512ec7fc8")); // make sure it exists (required for article reposistory test aswell)
        self::$sampleArticle = new Article(
            UUID::random(),
            self::$sampleUser,
            'sample header',
            'sample text'
        );
        $articleRepository->save(self::$sampleArticle);
    }

    public static function tearDownAfterClass() : void
    {
        $articleRepository = new ArticleRepositoryInterface(self::$mysql);
        $articleRepository->delete(self::$sampleArticle->id);
        self::$mysql->dispose();
    }

    #[TestWith(['00000000-0000-0000-0000-000000000000', '00000000-0000-0000-0000-000000000000'])] // unexistent article and user
    #[TestWith(['00000000-0000-0000-0000-000000000000', '1e54e6a0-6844-45a9-934d-9ec512ec7fc8'])] // unexistent article
    #[TestWith(['1b42fe64-a369-47f7-a269-360a3f785e5c', '00000000-0000-0000-0000-000000000000'])] // unexistent user
    public function testSaveFails(string $article_id, string $user_id)
    {
        if ($article_id != '00000000-0000-0000-0000-000000000000')
        {
            $articleRepository = new ArticleRepositoryInterface(self::$mysql);
            $article = $articleRepository->get(new UUID($article_id));
        }
        else
        {
            $article = new Article(
                new UUID($article_id),
                User::createRandom(),
                "unexistent article header",
                "unexistent article text"
            );
        }

        if ($user_id != '00000000-0000-0000-0000-000000000000')
        {
            $user = self::$mysql->getUser(new UUID($user_id));
        }
        else
        {
            $user = User::createRandom();
            $user->id = new UUID($user_id);
        }

        $like = new ArticleLike(
            UUID::random(),
            $article,
            $user
        );

        $this->expectException(Exception::class);
        self::$repo->save($like);
    }

    public function testGetUnexistingLike()
    {
        $fakeUuid = new UUID('00000000-0000-0000-0000-000000000000');

        $this->expectException(NotFoundException::class);
        self::$repo->get($fakeUuid);
    }

    public function testGetCountWhenZero()
    {
        $count = self::$repo->getCountByArticleUUID(self::$sampleArticle->id);
        $this->assertSame(0, $count);
    }

    #[DoesNotPerformAssertions]
    #[Depends('testGetCountWhenZero')]
    public function testSaveLike() : ArticleLike
    {
        $like = new ArticleLike(
            UUID::random(),
            self::$sampleArticle,
            self::$sampleUser
        );
        self::$repo->save($like);
        return $like;
    }

    #[Depends('testSaveLike')]
    public function testGetLike(ArticleLike $like) : ArticleLike
    {
        $dbLike = self::$repo->get($like->id);
        $this->assertEquals($like, $dbLike);
        return $like;
    }

    #[Depends('testGetLike')]
    public function testGetCountWhenOne(ArticleLike $like) : ArticleLike
    {
        $count = self::$repo->getCountByArticleUUID(self::$sampleArticle->id);
        $this->assertSame(1, $count);
        return $like;
    }

    #[Depends('testGetCountWhenOne')]
    public function testSameUserCantLeaveLike(ArticleLike $like) : ArticleLike
    {
        $duplicateLike = new ArticleLike(
            UUID::random(),
            self::$sampleArticle,
            self::$sampleUser
        );
        
        // $this->expectException() wont work here, since I need to return a value in the end of a method
        $exceptionThrown = false;
        try {
            self::$repo->save($duplicateLike);
        } catch (Exception $e) {
            $exceptionThrown = true;
            $this->assertInstanceOf(RepetitiveLikeException::class, $e);
        }
        $this->assertTrue($exceptionThrown);

        return $like;
    }

    #[Depends('testSameUserCantLeaveLike')]
    public function testDeleteLike(ArticleLike $like) : void
    {
        self::$repo->delete($like->id);

        $this->expectException(NotFoundException::class);
        self::$repo->get($like->id);
    }
}