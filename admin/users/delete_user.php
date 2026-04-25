<?php
// حماية الصفحة
require_once "../auth.php";

// الاتصال بقاعدة البيانات
require_once "../../includes/config.php";

//  التأكد من وجود id في الرابط وأنه رقم صحيح
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$userId = (int) $_GET['id'];

//  التحقق هل يوجد طلبات مرتبطة بهذا المستخدم
$checkSql  = "SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ?";
$checkStmt = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($checkStmt, "i", $userId);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);
$checkRow    = mysqli_fetch_assoc($checkResult);
mysqli_stmt_close($checkStmt);

if ($checkRow && $checkRow['cnt'] > 0) {
    // في طلبات مرتبطة بهذا المستخدم → لا نحذف
    $msg = urlencode("لا يمكن حذف هذا المستخدم لأنه مرتبط بعدد من الطلبات.).");
    header("Location: index.php?error={$msg}");
    exit;
}

//  حذف المستخدم من قاعدة البيانات
$sql  = "DELETE FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

//  الرجوع إلى صفحة المستخدمين مع رسالة نجاح
$ok = urlencode("تم حذف المستخدم بنجاح.");
header("Location: index.php?success={$ok}");
exit;