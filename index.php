<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/style.css">
    <title>Практикум №3 - Автозагрузчик</title>
</head>
<body>
<?php
spl_autoload_register(function($class_name) {
    $c = str_replace('/', DIRECTORY_SEPARATOR, $class_name);
    $c = str_replace('\\', DIRECTORY_SEPARATOR, $c);
    $c = str_replace('_', DIRECTORY_SEPARATOR, $c);
    $c .= '.php';

    if (!file_exists($c)) return;
    require $c;
});

require_once __DIR__ . "/vendor/autoload.php";

use Lab03\Models\IXenon;
use Lab03\Models\Meow;

class Xenon implements IXenon {
    public function __construct(Meow $meow) {
        $this->meow = $meow;
    }

    protected Meow $meow;

    public function do(string $doesDoDidnt) {
        $this->meow->Meow();
        ?><p><?php echo $doesDoDidnt ?></p><?php
    }
}

$xenon = new Xenon(new Meow());
$xenon->do("dont");

use Lab03\Models\User;
use Lab03\Models\Article;
use Lab03\Models\Comment;

$faker = Faker\Factory::create();
$articleAuthor = User::createRandom();
$article = new Article($faker->randomNumber(5, false), $articleAuthor, $faker->sentence(), $faker->text(3000));

?>
<div class="article-box">
    <h1><?php echo $article->header ?></h1>
    <h3>Author: <?php echo $article->author->fullName() ?></h3>
    <p><?php echo $article->text ?></p>
</div>
<?php

$comment_count = $faker->randomDigit();
for ($i=0; $i < $comment_count; $i++) {
    $commentAuthor = User::createRandom(); 
    $comment = new Comment($i, $commentAuthor, $article, $faker->text($faker->randomNumber(3, false)));

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