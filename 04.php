<?php


function simpleCost(array $items) {
    $cost = 0;
    foreach ($items as $item) {
        $cost += $item['price'] * $item['count'];
    }
    return $cost;
}

function createMonthCost ($day, $needle, $percent): Closure
{
    return function($cost) use ($day, $needle, $percent){
        if ($day == $needle) {
            return (1 - $percent / 100) * $cost;
        }

        return $cost;
    };
}

function randomCost($cost1, $cost2, $cost3) {
    if (1) {
        return $cost1;
    } elseif (2) {
        return $cost2;
    } else {
        return $cost3;
    }
}
#########
$monthCost = createMonthCost(date('d'), 15, 5);
#########


$items = [
    ['count' => 4, 'price' => 72],
    ['count' => 1, 'price' => 122],
];

#########

$cost = simpleCost($items);
$cost = $monthCost($cost, $items);

$cost1 = function ($cost, $items) use ($monthCost) {
    return $monthCost($cost, $items);
};
$cost2 = function ($cost, $items) use ($monthCost) {
    return $monthCost($cost, $items);
};
$cost3 = function ($cost, $items) use ($monthCost) {
    return $monthCost($cost, $items);
};

$randomCost = randomCost($cost1, $cost2, $cost3);
$cost = $randomCost($cost, $items);

echo $cost . PHP_EOL;