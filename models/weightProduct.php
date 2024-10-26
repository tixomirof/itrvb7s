<?php
require_once ('product.php');
class WeightProduct extends Product {
    public function __construct(float $initialCost, float $weightGramms) {
        parent::__construct($initialCost);
        if ($weightGramms < 1) die("Total weight of WeightProduct cannot be lower than 1.");
        $this->weightGramms = $weightGramms;
    }

    protected float $weightKG;

    public function getCost() {
        return $this->initialCost * $this->weightGramms / 1000.0;
    }
}
?>