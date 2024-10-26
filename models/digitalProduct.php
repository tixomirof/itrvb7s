<?php
require_once ('product.php');
class DigitalProduct extends Product {
    public function getCost() {
        return $this->initialCost / 2;
    }
}
?>