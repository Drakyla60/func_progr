<?php

function f1($a) {
    return $a * 2;
}

class C1 {
    static function f2($a) {
        return $a * 2;
    }
}

class C2 {
    function f3($a) {
        return $a * 2;
    }
}

$f1 = 'f1';
$f2 = 'C1::f2';
$f21 = ['C1','f2'];

$c2 = new C2();
$f3 = [$c2, 'f3'];

echo $f1(5) . PHP_EOL;
echo $f2(5) . PHP_EOL;
echo $f21(5) . PHP_EOL;
echo $f3(6) . PHP_EOL;
echo call_user_func($f2, 5) . PHP_EOL;
echo call_user_func_array($f2, [3, 5]) . PHP_EOL;




