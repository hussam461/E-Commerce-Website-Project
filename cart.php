<?php
session_start();
require_once "includes/config.php";

// جلب محتوى السلة من الجلسة
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// حساب الإجمالي الكلي
$grandTotal = 0;
foreach ($cartItems as $item) {
    $grandTotal += $item['price'] * $item['qty'];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>سلة المشتريات</title>
    <link rel="stylesheet" href="frontend/css/style.css?v=1.0">
</head>
<body class="fe-body">

<header class="fe-header">
    <div class="fe-logo">
        <span class="fe-logo-icon">A</span>
        <span class="fe-logo-text">متجري الإلكتروني</span>
    </div>

    <nav class="fe-nav">
        <a href="index.php" class="fe-nav-link">الرئيسية</a>
        <a href="product.php" class="fe-nav-link">المنتجات</a>
        <a href="cart.php" class="fe-nav-link active">سلة المشتريات</a>
        <a href="login.php" class="fe-nav-link fe-nav-auth">تسجيل الدخول</a>
    </nav>
</header>

<main class="fe-main">
    <div class="fe-container">

        <section class="fe-section">
            <div class="fe-section-header">
                <h2>سلة المشتريات</h2>
                <p>من هنا يمكنك مراجعة المنتجات التي أضفتها للسلة قبل إتمام الطلب.</p>
            </div>

            <div class="fe-cart-box">
                <?php if (empty($cartItems)): ?>
                    <p class="fe-note">السلة فارغة حالياً.</p>
                <?php else: ?>
                    <table class="fe-cart-table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>الصورة</th>
                            <th>المنتج</th>
                            <th>السعر للوحدة</th>
                            <th>الكمية</th>
                            <th>الإجمالي</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td>
                                    <?php if (!empty($item['image'])): ?>
                                        <img class="fe-cart-img"
                                             src="uploads/products/<?php echo htmlspecialchars($item['image']); ?>"
                                             alt="منتج">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fe-cart-name">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </div>
                                    <div class="fe-cart-category">
                                        التصنيف: <?php echo htmlspecialchars($item['category']); ?>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['price'], 2); ?> $</td>
                                <td><?php echo (int)$item['qty']; ?></td>
                                <td><?php echo number_format($item['price'] * $item['qty'], 2); ?> $</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="fe-cart-summary">
                        <span>الإجمالي الكلي:</span>
                        <strong><?php echo number_format($grandTotal, 2); ?> $</strong>
                    </div>

                    <div class="fe-cart-actions">
                        <a href="product.php" class="fe-btn-link">متابعة التسوق</a>
                        <a href="checkout.php" class="fe-btn-primary">إتمام الطلب</a>
                    </div>
                <?php endif; ?>
            </div>

        </section>

    </div>
</main>
<footer class="fe-footer">
    &copy; <?php echo date('Y'); ?> متجري الإلكتروني - جميع الحقوق محفوظة.
</footer>

</body>
</html>