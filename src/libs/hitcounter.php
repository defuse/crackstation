<?php

function getCrackedCount()
{
    $str = file_get_contents("count.dat");
    $counts = explode(":", $str);
    return (int)$counts[0];
}

function getCrackAttemptCount()
{
    $str = file_get_contents("count.dat");
    $counts = explode(":", $str);
    return (int)$counts[1];
}

function incrementCounter($cracked, $attempts)
{
    $str = (getCrackedCount() + $cracked) . ":" . (getCrackAttemptCount() + $attempts);
    file_put_contents("count.dat", $str);
}

?>
