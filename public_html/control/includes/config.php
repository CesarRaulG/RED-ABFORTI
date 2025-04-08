<?php
// Debugging
ini_set('error_reporting', E_ALL);

// DATABASE INFORMATION
define('DATABASE_HOST', '127.0.0.1:3306');
define('DATABASE_NAME', 'u975674050_vacci2');
define('DATABASE_USER', 'u975674050_vacciuser');
define('DATABASE_PASS', '21163708Va');


// CONNECT TO THE DATABASE
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

?>