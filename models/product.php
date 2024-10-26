<?php
abstract class Product {
    public function __construct(float $initialCost) {
        if ($initialCost < 0) die("Initial cost cannot be lower than 0.");
        $this->initialCost = $initialCost;
    }

    protected float $initialCost;
    abstract protected function getCost();

    public function sell($number) {
        return '<li>Товар #' . $number . ' был продан за: ' . $this->getCost() . ' руб.!</li>';
    }
}
?>