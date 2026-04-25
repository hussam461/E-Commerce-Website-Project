<?php
require_once "../auth.php";
require_once "../../includes/config.php";

// التأكد من وجود id في الرابط
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$order_id = (int) $_GET['id'];

// جلب بيانات الطلب + المستخدم
$sql = "SELECT orders.id, orders.total, orders.status, orders.created_at,
               users.name AS user_name, users.email
        FROM orders
        JOIN users ON orders.user_id = users.id
        WHERE orders.id = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    header("Location: index.php");
    exit;
}

// جلب المنتجات داخل الطلب
$itemSql = "SELECT order_items.quantity, order_items.price,
                   products.name AS product_name,
                   products.image
            FROM order_items
            JOIN products ON order_items.product_id = products.id
            WHERE order_items.order_id = ?";

$itemStmt = mysqli_prepare($conn, $itemSql);
mysqli_stmt_bind_param($itemStmt, "i", $order_id);
mysqli_stmt_execute($itemStmt);
$items = mysqli_stmt_get_result($itemStmt);
mysqli_stmt_close($itemStmt);

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تفاصيل الطلب #<?php echo $order['id']; ?></title>
    <link rel="stylesheet" href="../css/style.css?v=1.1">
</head>

<body class="dashboard-body">

<div class="dashboard-layout">

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">A</div>
            <div>
                <h2>لوحة التحكم</h2>
                <p><?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="../dashboard.php" class="nav-link">الرئيسية</a>
            <a href="../categories/index.php" class="nav-link">التصنيفات</a>
            <a href="../products/index.php" class="nav-link">المنتجات</a>
            <a href="index.php" class="nav-link active">الطلبات</a>
            <a href="../users/index.php" class="nav-link">المستخدمون</a>
        </nav>

        <a href="../logout.php" class="nav-link logout-link">تسجيل الخروج</a>
    </aside>

    <!-- المحتوى -->
    <main class="dashboard-main">

        <header class="dashboard-header">
            <h1>تفاصيل الطلب #<?php echo $order_id; ?></h1>
            <p>عرض تفاصيل الطلب والمنتجات المرتبطة به.</p>
        </header>

        <section class="dashboard-cards">
            <div class="dash-card wide">

                <!-- معلومات الطلب -->
                <div class="cat-header">
                    <h3>معلومات الطلب</h3>
                </div>

                <table class="cat-table">
                    <tbody>
                        <tr>
                            <th>رقم الطلب:</th>
                            <td><?php echo $order['id']; ?></td>
                        </tr>
                        <tr>
                            <th>اسم العميل:</th>
                            <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                        </tr>
                        <tr>
                            <th>البريد الإلكتروني:</th>
                            <td><?php echo htmlspecialchars($order['email']); ?></td>
                        </tr>
                        <tr>
                            <th>الحالة:</th>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                        </tr>
                        <tr>
                            <th>التاريخ:</th>
                            <td><?php echo $order['created_at']; ?></td>
                            </tr>
                        <tr>
                            <th>الإجمالي:</th>
                            <td><?php echo number_format($order['total'], 2); ?> $</td>
                        </tr>
                    </tbody>
                </table>

                <br>

                <!-- منتجات الطلب -->
                <div class="cat-header">
                    <h3>المنتجات داخل الطلب</h3>
                </div>

                <?php if (mysqli_num_rows($items) == 0): ?>
                    <p class="dash-note">لا توجد منتجات داخل هذا الطلب.</p>
                <?php else: ?>
                    <table class="cat-table">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>الصورة</th>
                                <th>السعر</th>
                                <th>الكمية</th>
                                <th>المجموع</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($item = mysqli_fetch_assoc($items)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>

                                <td>
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="../../uploads/products/<?php echo $item['image']; ?>"
                                             class="cat-image" alt="">
                                    <?php else: ?>
                                        <span class="dash-note">لا توجد صورة</span>
                                    <?php endif; ?>
                                </td>

                                <td><?php echo number_format($item['price'], 2); ?> $</td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($item['quantity'] * $item['price'], 2); ?> $</td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <br>

                <a href="index.php" class="btn-secondary">رجوع إلى الطلبات</a>

            </div>
        </section>

    </main>

</div>

</body>
</html>