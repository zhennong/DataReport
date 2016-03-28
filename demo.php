<?php

function test(& $a){
       $c = $a+100;
       echo $c;
}

$b = 1;



echo $b;

echo test($b);