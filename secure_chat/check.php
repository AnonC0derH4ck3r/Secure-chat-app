<?php
    date_default_timezone_set('Asia/Kolkata');
    echo password_hash('alice123', PASSWORD_DEFAULT) . "<br>";
    echo password_hash('bobpass', PASSWORD_DEFAULT) . "<br>";
    echo password_hash('charlie321', PASSWORD_DEFAULT) . "<br><br>";
    echo 'PHP timezone: ' . date_default_timezone_get();
    echo "<br><br>";
    echo date('Y-m-d H:i:s');
?>