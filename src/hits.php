<?php
    require_once('libs/hitcounter.php');
    require_once('libs/phpcount.php');
    echo "t: " . PHPCount::GetTotalHits();
    echo " u: " . PHPCount::GetTotalHits(true);
    echo " c: " . htmlspecialchars(getCrackedCount() . " of " . getCrackAttemptCount());
?>
