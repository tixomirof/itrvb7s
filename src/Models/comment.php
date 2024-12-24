<?php
namespace ITRvB\Models;

use ITRvB\Models\Article;
use ITRvB\Models\User;
use ITRvB\Models\UUID;

class Comment {
    public function __construct(UUID $id, User $author, Article $article, string $text) {
        $this->id = $id;
        $this->author = $author;
        $this->article = $article;
        $this->text = $text;
    }

    public UUID $id;
    public User $author;
    public Article $article;
    public string $text;
}