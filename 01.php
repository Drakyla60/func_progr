<?php

// Масив товарів (типу корзина)
$items = [
    ['count' => 4, 'price' => 72],
    ['count' => 1, 'price' => 122],
];

// Вартість
$cost = 0;

// Сумуємо всі товари
foreach ($items as $item) {
    $cost += $item['price'] * $item['count'];
}
//Якщо є товар від 100 до 150 грн то робимо знижку 9%
foreach ($items as $item) {
    if (100 <= $item['price'] && $item['price'] <= 150) {
        $cost -= (9 / 100) * $item['price'] * $item['count'];
    }
}
//Якщо сьогодні 15 число то робимо знижку 5%
if (date('d') == 28) {
    $cost = 0.95 * $cost;
}

//Якщо сума товарів більше ніж 1000 грн то робимо знижку 7%
if ($cost >= 1000) {
    $cost = 0.93 * $cost;
}
//Результат
echo $cost . PHP_EOL;

