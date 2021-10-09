<?php
/**
 * Сумуємо всі товари
 *
 * @param array $items Приймає товари
 * @return float|int
 */

function simpleCost(array $items) {
    $cost = 0;
    foreach ($items as $item) {
        $cost += $item['price'] * $item['count'];
    }
    return $cost;
}


/**
 * Якщо є товар від $min до $max грн то робимо знижку $percent %
 *
 * @param $min
 * @param $max
 * @param $percent
 * @return Closure
 */

//function priceBetweenCost($cost, array $items) {
//    global $min, $max, $percent;
//    $discount = 0;
//    foreach ($items as $item) {
//        if ($min <= $item['price'] && $item['price'] <= $max) {
//            $discount += ($percent / 100) * $item['price'] * $item['count'];
//        }
//    }
//    return $cost - $discount;
//}

function createPriceBetweenCost($min, $max, $percent): Closure
{
     return function ($cost, array $items) use ($min, $max, $percent) {
        $discount = 0;
        foreach ($items as $item) {
            if ($min <= $item['price'] && $item['price'] <= $max) {
                $discount += ($percent / 100) * $item['price'] * $item['count'];
            }
        }
        return $cost - $discount;
    };
}

/**
 * Якщо сьогодні $needle число то робимо знижку $percent %
 *
 * @param $day
 * @param $needle
 * @param $percent
 * @return Closure
 */
function createMonthCost ($day, $needle, $percent): Closure
{
    return function($cost) use ($day, $needle, $percent){
        if ($day == $needle) {
            return (1 - $percent / 100) * $cost;
        }

        return $cost;
    };
}


/**
 * Якщо сума товарів більше ніж $limit грн то робимо знижку $percent %
 *
 * @param $limit
 * @param $percent
 * @return float|int|mixed
 */

function createBigCost ($limit, $percent): Closure
{
    return function($cost) use ( $limit, $percent) {
        if ($cost >= $limit) {
            return (1 - $percent / 100) * $cost;
        }
        return $cost;
    };
}
################## Створення фабрик

$priceBetweenCost = createPriceBetweenCost(100, 150, 9);
$priceBetweenCost2 = createPriceBetweenCost(10, 15, 2);
$priceBetweenCost3 = createPriceBetweenCost(1000, 1500, 15);

$monthCost = createMonthCost(date('d'), 15, 5);

$bigCost = createBigCost(1000, 5);
##################

// Масив товарів (типу корзина)
$items = [
    ['count' => 4, 'price' => 72],
    ['count' => 1, 'price' => 122],
];

//Результат
$cost = simpleCost($items);
$cost = $priceBetweenCost($cost, $items);
$cost = $priceBetweenCost2($cost, $items);
$cost = $priceBetweenCost3($cost, $items);
$cost = $monthCost($cost);
$cost = $bigCost($cost);

echo $cost . PHP_EOL;

echo $bigCost($monthCost($priceBetweenCost(simpleCost($items), $items))) . PHP_EOL;

