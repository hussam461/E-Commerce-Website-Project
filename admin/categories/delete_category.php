<?php
require_once "../auth.php";

require_once "../../includes/config.php";

// 1) التأكد من وجود id في الرابط وأنه رقم صحيح
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$category_id = (int) $_GET['id'];

// 2) التحقق هل يوجد منتجات مرتبطة بهذا التصنيف
$checkSql  = "SELECT COUNT(*) AS cnt FROM products WHERE category_id = ?";
$checkStmt = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($checkStmt, "i", $category_id);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);
$checkRow    = mysqli_fetch_assoc($checkResult);
mysqli_stmt_close($checkStmt);

if ($checkRow && $checkRow['cnt'] > 0) {
    // في منتجات مرتبطة بهذا التصنيف  لا نحذف
    $msg = urlencode("لا يمكن حذف هذا التصنيف لأنه مرتبط بعدد من المنتجات. يرجى حذف المنتجات أو نقلها إلى تصنيف آخر أولاً.");
    header("Location: index.php?error={$msg}");
    exit;
}

// 3) جلب بيانات التصنيف أولاً (عشان نعرف إذا عنده صورة نحذفها)
$sql  = "SELECT image FROM categories WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$result   = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// لو ما فيه تصنيف بهذا id نرجع للقائمة
if (!$category) {
    header("Location: index.php");
    exit;
}

// 4) لو عنده صورة، نحذف ملف الصورة من المجلد
$uploadDir = "../../uploads/categories/";
if (!empty($category['image'])) {
    $imagePath = $uploadDir . $category['image'];
    if (is_file($imagePath)) {
        @unlink($imagePath); // نحذف الصورة، @ عشان ما يطلع تحذير لو ما لقاها
    }
}

// 5) حذف التصنيف من قاعدة البيانات
$sql  = "DELETE FROM categories WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// 6) الرجوع إلى صفحة التصنيفات بعد الحذف
header("Location: index.php");
exit;