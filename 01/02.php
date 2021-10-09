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
 * @param $cost
 * @param array $items
 * @param $min
 * @param $max
 * @param $percent
 * @return float|int
 */
function priceBetweenCost($cost, array $items, $min, $max, $percent) {
    $discount = 0;
    foreach ($items as $item) {
        if ($min <= $item['price'] && $item['price'] <= $max) {
            $discount += ($percent / 100) * $item['price'] * $item['count'];
        }
    }
    return $cost - $discount;
}

/**
 * Якщо сьогодні $needle число то робимо знижку $percent %
 *
 * @param $cost
 * @param $day
 * @param $needle
 * @param $percent
 * @return float|int|mixed
 */
function monthCost($cost, $day, $needle, $percent) {
    if ($day == $needle) {
        return (1 - $percent / 100) * $cost;
    }

    return $cost;
}


/**
 * Якщо сума товарів більше ніж $limit грн то робимо знижку $percent %
 *
 * @param $cost
 * @param $limit
 * @param $percent
 * @return float|int|mixed
 */
function bigCost($cost, $limit, $percent) {
    if ($cost >= $limit) {
        return (1 - $percent / 100) * $cost;
    }
    return $cost;
}


##################

// Масив товарів (типу корзина)
$items = [
    ['count' => 4, 'price' => 72],
    ['count' => 1, 'price' => 122],
];

//Результат
$cost = simpleCost($items);
$cost = priceBetweenCost($cost, $items, 100, 150, 9);
$cost = monthCost($cost, date('d'), 15, 5);
$cost = bigCost($cost, 1000, 3);

echo $cost . PHP_EOL;

