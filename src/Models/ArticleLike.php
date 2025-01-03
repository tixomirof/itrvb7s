<?php

namespace ITRvB\Models;

use ITRvB\Models\UUID;
use ITRvB\Models\Article;
use ITRvB\Models\User;

class ArticleLike
{
    public function __construct(UUID $id, Article $article, User $user)
    {
        $this->id = $id;
        $this->article = $article;
        $this->user = $user;
    }

    public UUID $id;
    public Article $article;
    public User $user;
}