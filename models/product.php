<?php
class Product {
    function __construct($name, $cost, $description) {
        $this->name = $name;
        $this->cost = $cost;
        $this->description = $description;
    }

    public string $name;
    public float $cost;
    public string $description;
    public $reviews;

    public function show() {
        ?>
        <div class="product" style='text-align:center; border:3px solid gray;'>
            <h3><?php echo $this->name ?></h3>
            <p><?php echo $this->description ?></p>
            <span><?php echo $this->cost ?> ₽</span>
        </div>
        <?php
    }

    public function addReview(Review $review) {
        array_push($this->reviews, $review);
    }

    public function order(User $user) {
        $user->cart->addProduct($this);
    }

    public function getRating() {
        $result = 0.0;
        if (count($this->reviews) === 0) return $result;
        foreach ($this->reviews as $review) {
            $result += $review->rating;
        }
        return $result / count($this->reviews);
    }
}
?>