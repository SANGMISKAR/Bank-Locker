<?php
// Database connection
$host = 'banklockermanagemanet-server.mysql.database.azure.com';
$username = 'fmpvmesakh';
$password = 'dMWGr9$tBzpRZVIR';
$dbname = 'banklockermanagemanet';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>