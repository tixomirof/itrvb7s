<?php
namespace Lab03\Models;

use Lab03\Models\User;

class Article {
    public function __construct(int $id, User $author, string $header, string $text) {
        $this->id = $id;
        $this->author = $author;
        $this->header = $header;
        $this->text = $text;
    }

    public int $id;
    public User $author;
    public string $header;
    public string $text;
}
