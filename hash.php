<?php
$password = 'azzam123';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo $hash; 

?>

// Put your password in the 'password' variable, then run hash.php to generate the encrypted password that will later be used in phpMyAdmin.
