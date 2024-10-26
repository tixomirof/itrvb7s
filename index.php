<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Практика №2</title>

    <style>
        .results {
            border: 3px dashed black;
            padding: 0 10px;
            position: absolute;
            left: 50%;
            top: 50%;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
            box-shadow: 0 0 20px black;
            background-color: white;
        }

        body {
            padding: 0;
            margin: 0;
            background-color: lightgray;
        }
    </style>
</head>
<body>
<?php
require ('./models/digitalProduct.php');
require ('./models/physicalProduct.php');
require ('./models/weightProduct.php');

$digitalProduct1 = new DigitalProduct(320);
$digitalProduct2 = new DigitalProduct(1700.5);
$digitalProduct3 = new DigitalProduct(15.256691);

$physicalProduct1 = new PhysicalProduct(320, 1);
$physicalProduct2 = new PhysicalProduct(320, 45);
$physicalProduct3 = new PhysicalProduct(412.525, 3);

$weightProduct1 = new WeightProduct(320, 1000);
$weightProduct2 = new WeightProduct(144.44, 3447);
$weightProduct3 = new WeightProduct(4185818, 1);

// подсчет дохода с продаж
?>
<div class="results">
    <h2>Подсчет дохода с продаж товаров:</h2>
    <ul>
        <?php
        echo $digitalProduct1->sell(1);
        echo $digitalProduct2->sell(2);
        echo $digitalProduct3->sell(3);
        echo $physicalProduct1->sell(4);
        echo $physicalProduct2->sell(5);
        echo $physicalProduct3->sell(6);
        echo $weightProduct1->sell(7);
        echo $weightProduct2->sell(8);
        echo $weightProduct3->sell(9);
        ?>
    </ul>
</div>
<?php
?>
</body>
</html>