<?php
require_once "../includes/config.php";


$username = "admin";     
$Pass = "123456";    

$hashedPass = password_hash($Pass, PASSWORD_DEFAULT);

$sql  = "INSERT INTO admins (username, password) VALUES (?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $username, $hashedPass);

if (mysqli_stmt_execute($stmt)) {
 echo "success";
} else {
    echo " خطأ: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);