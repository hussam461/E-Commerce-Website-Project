<?php
require_once "../auth.php";
require_once "../../includes/config.php";

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = (int) $_GET['id'];

$sql  = "SELECT image FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result  = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// لو المنتج غير موجود  
if (!$product) {
    header("Location: index.php");
    exit;
}

//  حذف الصورة من uploads/products
$uploadDir = "../../uploads/products/";

if (!empty($product['image'])) {
    $imagePath = $uploadDir . $product['image'];
    if (is_file($imagePath)) {
        @unlink($imagePath);
    }
}

$sql  = "DELETE FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: index.php");
exit;