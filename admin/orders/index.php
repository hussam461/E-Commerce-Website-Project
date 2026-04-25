<?php
require_once "../auth.php";
require_once "../../includes/config.php";


// جلب الطلبات مع بيانات المستخدم
$sql = "SELECT orders.id, orders.total, orders.status, orders.created_at,
               users.name AS user_name
        FROM orders
        JOIN users ON orders.user_id = users.id
        ORDER BY orders.id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الطلبات</title>
    <link rel="stylesheet" href="../css/style.css?v=1.1">
</head>
<body class="dashboard-body">

<div class="dashboard-layout">

    <!-- الشريط الجانبي -->
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
            <h1>إدارة الطلبات</h1>
            <p>من هنا يمكنك استعراض الطلبات الخاصة بالمستخدمين.</p>
        </header>

        <section class="dashboard-cards">
            <div class="dash-card wide">

                <div class="cat-header">
                    <h3>قائمة الطلبات</h3>
                </div>

                <?php if (mysqli_num_rows($result) == 0): ?>

                    <p class="dash-note">لا يوجد أي طلب حتى الآن.</p>

                <?php else: ?>

                    <table class="cat-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم العميل</th>
                                <th>المجموع</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>عرض</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>

                                <td><?php echo htmlspecialchars($row['user_name']); ?></td>

                                <td><?php echo number_format($row['total'], 2); ?> $</td>

                                <td><?php echo htmlspecialchars($row['status']); ?></td>

                                <td><?php echo $row['created_at']; ?></td>

                                <td>
                                    <a href="order_details.php?id=<?php echo $row['id']; ?>" class="table-link">
                                        عرض التفاصيل
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>

                    </table>
                <?php endif; ?>

            </div>
        </section>

    </main>
</div>

</body>
</html>