<?php

/**
 * @return Closure
 */
function createSimpleCost(): Closure
{
    return function (array $items) {
        $itemCost = function ($item) {
            return $item['price'] * $item['count'];
        };

        return array_sum(
            array_map($itemCost, $items));
    };
}

/**
 * @param callable $next
 * @param $min
 * @param $max
 * @param $percent
 * @return Closure
 */
function createPriceBetweenCost(callable $next, $min, $max, $percent): Closure
{
    return function (array $items) use ($next, $min, $max, $percent) {
        $discount = function ($item) use ($percent) { return ($percent / 100) * $item['price'] * $item['count']; };
        $filter = function ($item) use ($min, $max) { return $min <= $item['price'] && $item['price'] <= $max; };

        return $next($items) - array_sum(
                array_map($discount,
                    array_filter($items, $filter)));
    };
}

/**
 * @param callable $next
 * @param $day
 * @param $needle
 * @param $percent
 * @return Closure
 */
function createMonthCost(callable $next, $day, $needle, $percent): Closure
{
    return function (array $items) use ($next, $day, $needle, $percent) {
        return ($day == $needle) ? (1 - $percent / 100) * $next($items) : $next($items);
    };
}

/**
 * @param callable $next
 * @param $limit
 * @param $percent
 * @return Closure
 */
function createBigCost(callable $next, $limit, $percent): Closure
{
    return function (array $items) use ($next, $limit, $percent) {
        $cost = $next($items);
        return ($cost >= $limit) ? (1 - $percent / 100) * $cost : $cost;
    };
}

/**
 * @param callable $next
 * @param $day
 * @param $birthday
 * @param $percent
 * @return Closure
 */
function createBirthdayCost(callable $next, $day, $birthday, $percent): Closure
{
    return function (array $items) use ($next, $day, $birthday, $percent) {
        return ($day == $birthday) ? (1 - $percent / 100) * $next($items) : $next($items);
    };
}

/**
 * @param array $costs
 * @return Closure
 */
function createMinCost(array $costs): Closure
{
    return function (array $items) use ($costs) {
        return min(
            array_map(function ($cost) use ($items) { return $cost($items); }, $costs));
    };
}

##################################

$simpleCost = createSimpleCost();
$priceBetweenCost = createPriceBetweenCost($simpleCost, 100, 150, 9);

$monthCost = createMonthCost($priceBetweenCost, date('d'), 15, 5);
$birthdayCost = createBirthdayCost($priceBetweenCost, date('m-d'), '08-12', 6);

$minCost = createMinCost([$monthCost, $birthdayCost]);
$cost = createBigCost($minCost, 1000, 7);

##################################

$items = [
    ['count' => 2, 'price' => 75],
    ['count' => 5, 'price' => 150],
];

echo $cost($items) . PHP_EOL;