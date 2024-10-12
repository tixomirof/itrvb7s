<?php
class Review {
    function __construct(User $author, string $text, int $rating) {
        if ($rating > 5 || $rating <= 0) {
            die('Rating cannot be lower than 1 or greater than 5');
        }

        $this->author = $author;
        $this->text = $text;
        $this->rating = $rating;
    }

    public int $rating;
    public User $author;
    public string $text;
}
?>