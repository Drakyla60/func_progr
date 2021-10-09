<?php

$items = [
    ['count' => 4, 'price' => 72],
    ['count' => 1, 'price' => 122],
    ['count' => 1, 'price' => 121],
];

$sum = function ($total, $current) {
    return $total + $current;
};
$discount = function ($item) {
    return 0.95 * $item['price'] * $item['count'];
};
$filter = function ($item) {
    return 100 <= $item['price'] && $item['price'] <= 150;
};
/**
 * array_filter функція яка фільтрує масив, приймає 2 паарметри
 * 1 масив який тре пофільтруввати
 * 2 фільтр яким фільтруємо
 */
$one = array_filter($items, $filter);
/**
 * array_map функція яка застосовує callback функцію до кожного елемента масиву, яка приймає 2 параметри
 * 1 callback функція
 * 2 масив
 */
$two = array_map($discount, $one);

/**
 * array_reduce функція яка застосовує callback функцію для кожного елемента масиву,
 * так щоб осталося одне значення (в моєму випадку сусує елементи масива)
 * 1 масив
 * 2 callback функція
 */
echo array_reduce($two, $sum);

//echo array_reduce( array_map( $discount, array_filter($items, $filter)), $sum);
