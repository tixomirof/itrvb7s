<?php
namespace ITRvB\Models;

use ITRvB\Models\User;
use ITRvB\Models\UUID;

class Article {
    public function __construct(UUID $id, User $author, string $header, string $text) {
        $this->id = $id;
        $this->author = $author;
        $this->header = $header;
        $this->text = $text;
    }

    public UUID $id;
    public User $author;
    public string $header;
    public string $text;
}
