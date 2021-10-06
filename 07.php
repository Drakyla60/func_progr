<?php
// Порахувати суму значень в масиві

/**
 * array_slice() робить зріз масива з 0 першого до -1 передостаннього
 * @param array $items
 * @return array
 */
function left(array $items): array
{
    return array_slice($items, 0, -1);
}

/**
 * end() ставить вказівник на останє значеня масива
 * reset() ставить вказівник на перше значеня масива
 * @param array $items
 * @return false|mixed
 */
function sum(array $items) {
    if (count($items) > 1) {
        return sum(left($items)) + end($items);
    } else {
        return reset($items);
    }
}

$items = [5, 4, 3, 2, 1];


echo sum($items) . PHP_EOL;

