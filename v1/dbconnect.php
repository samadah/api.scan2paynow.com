<?php
$mysqli = new mysqli('localhost', 'root', '', 'alertco_new');
//
if(mysqli_connect_errno()) {
    echo "Connection Failed. Please return later: " . mysqli_connect_errno();
    exit();
   }
?>