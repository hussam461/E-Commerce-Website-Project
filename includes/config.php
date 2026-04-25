<?php
$host     = "localhost";
$user     = "root";          
$password = "";      
$dbname   = "db_mystore";    

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

// ضبط الترميز
mysqli_set_charset($conn, "utf8mb4");
?>
 