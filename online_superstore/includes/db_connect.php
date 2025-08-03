<?php
$mysqli = new mysqli('localhost', 'root', '', 'superstore');
if ($mysqli->connect_error) { die('Connection Error'); }
?>