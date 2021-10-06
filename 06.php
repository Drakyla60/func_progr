<?php

$simpleCost = function (array $items) {
    $cost = 0;
    foreach ($items as $item) {
        $cost += $item['price'] * $item['count'];
    }
    return $cost;
};

function createPriceBetweenCost(callable $nextFunction, $min, $max, $percent): Closure
{
    return function (array $items) use ($nextFunction, $min, $max, $percent) {
        $discount = 0;
        foreach ($items as $item) {
            if ($min <= $item['price'] && $item['price'] <= $max) {
                $discount += ($percent / 100) * $item['price'] * $item['count'];
            }
        }
        return $nextFunction($items) - $discount;
    };
}

function createMonthCost(callable $nextFunction, $day, $needle, $percent): Closure
{
    return function (array $items) use ($nextFunction, $day, $needle, $percent) {
        return ($day == $needle) ? (1 - $percent / 100) * $nextFunction($items) : $nextFunction($items);
    };
}

function createBigCost(callable $nextFunction, $limit, $percent): Closure
{
    return function (array $items) use ($nextFunction, $limit, $percent) {
        $cost = $nextFunction($items);
        return ($cost >= $limit) ? (1 - $percent / 100) * $cost : $cost;
    };
}

#################

$priceBetweenCost = createPriceBetweenCost($simpleCost, 100, 150, 9);
$monthCost = createMonthCost($priceBetweenCost, date('d'), 15, 5);
$cost = createBigCost($monthCost, 1000, 7);

#################

$items = [
    ['count' => 4, 'price' => 72],
    ['count' => 1, 'price' => 122],
];

$items2 = [
    ['count' => 4, 'price' => 72],
    ['count' => 12, 'price' => 122],
];

echo $cost($items) . PHP_EOL;
echo $cost($items2) . PHP_EOL;