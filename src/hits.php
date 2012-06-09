<?php
    require_once('libs/hitcounter.php');
    require_once('libs/phpcount.php');
    echo "t: " . number_format(PHPCount::GetTotalHits(), 0);
    echo " u: " . number_format(PHPCount::GetTotalHits(true), 0);
    echo " c: " . htmlspecialchars(number_format(getCrackedCount(), 0) . " of " . number_format(getCrackAttemptCount(), 0));
?>
