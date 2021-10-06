<?php
function createSimpleCost(): Closure
{
    return function (array $items) {
        $cost = 0;
        foreach ($items as $item) {
            $cost += $item['price'] * $item['count'];
        }
        return $cost;
    };
}

function createPriceBetweenCost(callable $next, $min, $max, $percent): Closure
{
    return function (array $items) use ($next, $min, $max, $percent) {
        $discount = 0;
        foreach ($items as $item) {
            if ($min <= $item['price'] && $item['price'] <= $max) {
                $discount += ($percent / 100) * $item['price'] * $item['count'];
            }
        }
        return $next($items) - $discount;
    };
}

function createMonthCost(callable $next, $day, $needle, $percent): Closure
{
    return function (array $items) use ($next, $day, $needle, $percent) {
        return ($day == $needle) ? (1 - $percent / 100) * $next($items) : $next($items);
    };
}

function createBigCost(callable $next, $limit, $percent): Closure
{
    return function (array $items) use ($next, $limit, $percent) {
        $cost = $next($items);
        return ($cost >= $limit) ? (1 - $percent / 100) * $cost : $cost;
    };
}

#################
$simpleCost = createSimpleCost();
$priceBetweenCost = createPriceBetweenCost($simpleCost, 100, 150, 9);
$monthCost = createMonthCost($priceBetweenCost, date('d'), 15, 5);
$cost = createBigCost($monthCost, 1000, 7);

#################

$items = [
    ['count' => 4, 'price' => 72],
    ['count' => 1, 'price' => 122],
];

echo $cost($items) . PHP_EOL;