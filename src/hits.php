<?php
    require_once('libs/hitcounter.php');
    echo htmlspecialchars(getCrackedCount() . " of " . getCrackAttemptCount());
?>
