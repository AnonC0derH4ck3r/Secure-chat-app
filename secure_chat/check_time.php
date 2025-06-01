<?php
    $last_attempt = "2025-05-24 19:43:48";
    $lockout_minutes = 15;
    $last_attempt_to_unix_timestamp = strtotime($last_attempt);
    $fifteen_minutes_before = strtotime("-{$lockout_minutes} minutes");
    if($last_attempt_to_unix_timestamp > $fifteen_minutes_before)
        echo "Less than fifteen minutes ago";
    else
        echo "More than fifteen minutes.";
?>