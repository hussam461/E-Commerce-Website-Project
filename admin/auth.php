<?php
// نبدأ السيشن عشان نقدر نقرأ بيانات الأدمن
session_start();

// لو ما في أدمن مسجّل دخوله
if (!isset($_SESSION["admin_id"])) {
    
    header("Location: login.php");
    exit;
}