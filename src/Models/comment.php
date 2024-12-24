<?php
namespace Lab03\Models;

use Lab03\Models\Article;
use Lab03\Models\User;

class Comment {
    public function __construct(int $id, User $author, Article $article, string $text) {
        $this->id = $id;
        $this->author = $author;
        $this->article = $article;
        $this->text = $text;
    }

    public int $id;
    public User $author;
    public Article $article;
    public string $text;
}