<?php
require_once ('product.php');
class PhysicalProduct extends Product {
    public function __construct(float $initialCost, int $count) {
        parent::__construct($initialCost);
        if ($count < 1) die ("Physical product's count cannot be lower than 1.");
        $this->count = $count;
    }

    protected int $count;

    public function getCost() {
        return $this->initialCost * $this->count;
    }
}
?>