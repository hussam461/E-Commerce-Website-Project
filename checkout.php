<?php
session_start();
require_once "includes/config.php";

//  لازم يكون المستخدم مسجّل دخول
if (!isset($_SESSION['user_id'])) {
    // نخزن الصفحة اللي كان رايح لها
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header("Location: login.php");
    exit;
}

//  نقرأ السلة من الجلسة
$cart = $_SESSION['cart'] ?? [];

// لو السلة فاضية نرجعه لصفحة السلة
if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

$errors  = [];
$success = "";

// نحسب الإجمالي الكلي من السلة
$grandTotal = 0;
foreach ($cart as $item) {
    $grandTotal += $item['price'] * $item['qty'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userId = $_SESSION['user_id'];

    // نتحقق مرة ثانية أن السلة مازالت مش فاضية (احتياط)
    if (empty($cart)) {
        $errors[] = "السلة فارغة، لا يمكن إنشاء طلب.";
    }

    if (empty($errors)) {

        // نستخدم معاملة (Transaction) عشان كل الإدخالات تتم أو تُلغى معاً
        mysqli_begin_transaction($conn);

        try {
            //  إدخال الطلب في جدول orders
            $status = 'pending'; // مثلاً "معلّق"
            $orderSql  = "INSERT INTO orders (user_id, total, status) VALUES (?, ?, ?)";
            $orderStmt = mysqli_prepare($conn, $orderSql);
            mysqli_stmt_bind_param($orderStmt, "ids", $userId, $grandTotal, $status);
            mysqli_stmt_execute($orderStmt);

            // اخر رقم تم توليده لهذا لاتصال
            $orderId = mysqli_insert_id($conn);
            mysqli_stmt_close($orderStmt);

            //  إدخال عناصر الطلب في order_items
            $itemSql  = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                         VALUES (?, ?, ?, ?)";
            $itemStmt = mysqli_prepare($conn, $itemSql);

            foreach ($cart as $item) {
                $productId = $item['id'];
                $qty       = $item['qty'];
                $price     = $item['price'];

                mysqli_stmt_bind_param($itemStmt, "iiid", $orderId, $productId, $qty, $price);
                mysqli_stmt_execute($itemStmt);
            }

            mysqli_stmt_close($itemStmt);

            //  لو كل شيء تمام  نثبت (COMMIT)
            mysqli_commit($conn);

            // نفرغ السلة
            unset($_SESSION['cart']);

            // رسالة نجاح
            $success = "تم إنشاء طلبك بنجاح! رقم الطلب: #" . $orderId;

        } catch (Exception $e) {
            // لو صار أي خطأ  نرجع كل شيء كما كان
            mysqli_rollback($conn);
            $errors[] = "حدث خطأ أثناء إنشاء الطلب. حاول مرة أخرى.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إتمام الطلب</title>
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
        <a href="cart.php" class="fe-nav-link">السلة</a>
        <a href="login.php" class="fe-nav-link fe-nav-auth">تسجيل الدخول</a>
    </nav>
</header>

<main class="fe-main">
    <div class="fe-container">

        <section class="fe-section">
            <div class="fe-section-header">
                <h2>إتمام الطلب</h2>
                <p>راجع منتجاتك قبل تأكيد الطلب.</p>
            </div>

            <div class="fe-cart-box">

                <?php if (!empty($errors)): ?>
                    <div class="fe-alert-error">
                        <?php foreach ($errors as $e): ?>
                            <div><?php echo $e; ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="fe-alert-success">
                        <?php echo $success; ?>
                    </div>
                    <div class="fe-cart-actions">
                        <a href="index.php" class="fe-btn-link">الرجوع للرئيسية</a>
                        <a href="product.php" class="fe-btn-primary">متابعة التسوق</a>
                    </div>
                <?php else: ?>

                    <!-- جدول مراجعة السلة قبل التأكيد -->
                    <table class="fe-cart-table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>المنتج</th>
                            <th>السعر للوحدة</th>
                            <th>الكمية</th>
                            <th>الإجمالي</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($cart as $item): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
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

                    <!-- زر تأكيد الطلب -->
                    <form method="POST" style="margin-top:15px; text-align:left;">
                        <button type="submit" class="fe-btn-primary">
                            تأكيد الطلب
                        </button>
                    </form>

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