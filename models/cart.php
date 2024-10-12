<?php
class Cart {
    function __construct(User $owner) {
        $this->owner = $owner;
        $this->products = array();
    }

    public User $owner;
    public $products;

    public function addProduct(Product $product) {
        array_push($this->products, $product);
    }

    public function removeProduct(int $productIndex) {
        if (count($this->products) === 0) {
            die('No products');
        } elseif (count($this->products) >= $productIndex) {
            die('Index out of bounds');
        } else {
            array_splice($this->products, $productIndex, 1);
        }
    }

    public function clear() {
        $this->products = array();
    }

    public function show() {
        ?>
        <div class="cart">
        <?php
        foreach ($this->products as $product) {
            $product->show();
        }
        ?></div><?php
    }

    public function getTotalCost() {
        $result = 0.0;
        foreach ($this->products as $product) {
            $result += $product->cost;
        }
        return $result;
    }
}
?>