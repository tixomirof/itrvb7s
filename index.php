<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/style.css">
    <title>Практикум №4 - Репозитории и UUID</title>
</head>
<body>
<?php
require_once __DIR__ . "/vendor/autoload.php";

use ITRvB\Models\User;
use ITRvB\Models\Article;
use ITRvB\Models\Comment;
use ITRvB\Models\UUID;

$faker = Faker\Factory::create();
$articleAuthor = User::createRandom();
$article = new Article(UUID::random(), $articleAuthor, $faker->sentence(), $faker->text(3000));

?>
<div class="article-box">
    <h1><?php echo $article->header ?></h1>
    <h3>UUID: <?php echo $article->id ?></h3>
    <h3>Author: <?php echo $article->author->fullName() ?></h3>
    <p><?php echo $article->text ?></p>
</div>
<?php

$comment_count = $faker->randomDigit();
for ($i=0; $i < $comment_count; $i++) {
    $commentAuthor = User::createRandom(); 
    $comment = new Comment(UUID::random(), $commentAuthor, $article, $faker->text($faker->randomNumber(3, false)));

    ?>
    <div class="comment-box">
        <h3><?php echo $comment->author->fullName(); ?></h3>
        <?php
        echo $comment->text;
        ?>
    </div>
    <?php
}
?>
</body>
</html>