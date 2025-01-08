<?php

namespace ITRvB\Repositories\Seeders;

use Faker\Factory as F;
use ITRvB\Repositories\ArticleRepositoryInterface;
use ITRvB\Repositories\CommentRepositoryInterface;
use ITRvB\Repositories\Connection\MySQL;
use ITRvB\Models\User;
use ITRvB\Models\Article;
use ITRvB\Models\Comment;
use ITRvB\Models\UUID;
use ITRvB\Exceptions\ArgumentException;

class DataSeeder
{
    public function __construct(
        private readonly MySQL $mysql,
    )
    {
    }

    public function seed(int $userNumber, int $articleNumber) : array
    {
        if ($userNumber < 1) throw new ArgumentException('Cannot generate less than 1 user in DataSeeder');
        if ($articleNumber < 0) throw new ArgumentException('Cannot generate less than 0 articles in DataSeeder');

        $users = array();
        $articles = array();
        $comments = array();

        $faker = F::create();
        
        for ($i=0; $i < $userNumber; $i++) { 
            $user = User::createRandom();
            $this->mysql->addUser($user);
            array_push($users, $user);
        }

        $articleRepository = new ArticleRepositoryInterface($this->mysql);
        $commentRepository = new CommentRepositoryInterface($this->mysql);
        for ($i=0; $i < $articleNumber; $i++) { 
            $article = new Article(
                UUID::random(),
                $this->getRandom($users),
                $faker->sentence(),
                $faker->text(3000)
            );
            $articleRepository->save($article);
            array_push($articles, $article);

            $commentCount = $faker->randomDigit();
            for ($j=0; $j < $commentCount; $j++) {
                $commentAuthor = $this->getRandom($users);
                $comment = new Comment(UUID::random(), $commentAuthor, $article, $faker->text($faker->randomNumber(3, false)));
        
                $commentRepository->save($comment);
                array_push($comments, $comment);
            }
        }

        return [
            'users' => $users,
            'articles' => $articles,
            'comments' => $comments
        ];
    }

    private function getRandom(array $array)
    {
        return $array[array_rand($array)];
    }
}