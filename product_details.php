<?php
// نبدأ الجلسة عشان نقدر نستخدم السلة
session_start();

// الاتصال بقاعدة البيانات
require_once  "includes/config.php";



$productId = (int) $_GET['id'];

// لو تم إرسال نموذج "إضافة إلى السلة"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {

    // الكمية (لو ما أرسل شيء أو كانت غلط، نخليها)
    $qty = 1;
    if (isset($_POST['qty']) && ctype_digit($_POST['qty']) && (int)$_POST['qty'] > 0) {
        $qty = (int) $_POST['qty'];
    }

    // جلب بيانات المنتج  (عشان نتأكد أنه موجود ونأخذ البيانات)
    $sql  = "SELECT p.id, p.name, p.price, p.image, p.description, c.name AS category_name
             FROM products p
             JOIN categories c ON p.category_id = c.id
             WHERE p.id = ?
             LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // لو المنتج مش موجود نرجع لصفحة المنتجات
    if (!$product) {
        header("Location: product.php");
        exit;
    }

    // تجهيز السلة داخل SESSION
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // لو المنتج موجود من قبل في السلة  نزيد الكمية
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['qty'] += $qty;
    } else {
        // لو أول مرة يضاف للسلة
        $_SESSION['cart'][$productId] = [
            'id'       => $product['id'],
            'name'     => $product['name'],
            'price'    => $product['price'],
            'image'    => $product['image'],
            'qty'      => $qty,
            'category' => $product['category_name'],
        ];
    }

    // بعد الإضافة نودّي المستخدم إلى صفحة السلة
    header("Location: cart.php");
    exit;
}

//  لو ما في POST  جلب بيانات المنتج لعرضها في الصفحة
$sql  = "SELECT p.id, p.name, p.price, p.image, p.description, c.name AS category_name
         FROM products p
         JOIN categories c ON p.category_id = c.id
         WHERE p.id = ?
         LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);
$result  = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// لو المنتج غير موجود
if (!$product) {
    header("Location: product.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?> - تفاصيل المنتج</title>
    <link rel="stylesheet" href="frontend/css/style.css?v=2.0">
</head>
<body class="fe-body">

<header class="fe-header">
    <div class="fe-logo">
        <span class="fe-logo-icon">A</span>
        <span class="fe-logo-text">متجري الإلكتروني</span>
    </div>

    <nav class="fe-nav">
        <a href="index.php" class="fe-nav-link">الرئيسية</a>
        <a href="product.php" class="fe-nav-link active">المنتجات</a>
        <a href="cart.php" class="fe-nav-link">سلة المشتريات</a>
        <a href="login.php" class="fe-nav-link fe-nav-auth">تسجيل الدخول</a>
    </nav>
</header>

<main class="fe-main">
    <div class="fe-container">

        <section class="fe-section">
            <div class="fe-section-header">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p>التصنيف: <?php echo htmlspecialchars($product['category_name']); ?></p>
            </div>

            <div class="fe-product-details">
                <!-- عمود الصورة -->
                <div class="fe-product-details-image">
                    <?php if (!empty($product['image'])): ?>
                        <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="صورة المنتج">
                    <?php else: ?>
                        <div class="fe-placeholder-img">لا توجد صورة</div>
                    <?php endif; ?>
                </div>

                <!-- عمود المعلومات -->
                <div class="fe-product-details-info">
                    <div class="fe-product-details-price">
                        السعر:
                        <span><?php echo number_format($product['price'], 2); ?> $</span>
                    </div>

                    <div class="fe-product-details-desc">
                        <h3>الوصف</h3>
                        <p>
                            <?php
                            if (!empty($product['description'])) {
                                echo nl2br(htmlspecialchars($product['description']));
                            } else {
                                echo "لا يوجد وصف متوفر لهذا المنتج حالياً.";
                            }
                            ?>
                        </p>
                    </div>

                    <form method="post" class="fe-product-details-form">
                        <label for="qty">الكمية:</label>
                        <input type="number" id="qty" name="qty" min="1" value="1">

                        <button type="submit" name="add_to_cart" class="fe-btn-primary">
                            إضافة إلى السلة
                        </button>
                    </form>

                    <div class="fe-product-details-back">
                        <a href="product.php" class="fe-btn-link">الرجوع لصفحة المنتجات</a>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>
<footer class="fe-footer">
    &copy; <?php echo date('Y'); ?> متجري الإلكتروني - جميع الحقوق محفوظة.
</footer>

</body>
</html>