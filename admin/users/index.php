<?php
require_once "../auth.php";

require_once "../../includes/config.php";

// 3) جلب المستخدمين مع عدد الطلبات لكل مستخدم
$sql = "
    SELECT 
        u.id,
        u.name,
        u.email,
        u.created_at,
        COUNT(o.id) AS orders_count
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id
    GROUP BY u.id, u.name, u.email, u.created_at
    ORDER BY u.id DESC
";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die('خطأ في جلب المستخدمين: ' . mysqli_error($conn));
}

// رسائل نجاح / خطأ
$errorMsg   = $_GET['error']   ?? null;
$successMsg = $_GET['success'] ?? null;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة المستخدمين</title>
    <link rel="stylesheet" href="../css/style.css?v=1.0">
</head>
<body class="dashboard-body">

<div class="dashboard-layout">


     <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">A</div>
            <div>
                <h2>لوحة التحكم</h2>
            <!--يطبع اسم الادمن-->
                <p><?php echo htmlspecialchars($_SESSION["admin_username"]); ?></p>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="../dashboard.php" class="nav-link">الرئيسية</a>
            <a href="../categories/index.php" class="nav-link">التصنيفات</a>
            <a href="../products/index.php" class="nav-link">المنتجات</a>
            <a href="../orders/index.php" class="nav-link">الطلبات</a>
            <a href="index.php" class="nav-link active">المستخدمون</a>
        </nav>

        <div class="sidebar-footer">
            <a href="../logout.php" class="nav-link logout-link">تسجيل الخروج</a>
        </div>
    </aside>

    <!-- محتوى الصفحة -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <h1>إدارة المستخدمين</h1>
            <p>من هنا يمكنك استعراض المستخدمين المسجلين في المتجر.</p>
        </header>

        <section class="dashboard-cards">
            <div class="dash-card wide">

                <div class="cat-header">
                    <h3>قائمة المستخدمين</h3>
                </div>

                <?php if ($errorMsg): ?>
                    <p class="dash-note" style="color:#fca5a5;">
                        <?php echo htmlspecialchars($errorMsg); ?>
                    </p>
                <?php endif; ?>

                <?php if ($successMsg): ?>
                    <p class="dash-note" style="color:#4ade80;">
                        <?php echo htmlspecialchars($successMsg); ?>
                    </p>
                <?php endif; ?>

                <?php if (mysqli_num_rows($result) === 0): ?>

                    <p class="dash-note">لا يوجد أي مستخدم حتى الآن.</p>

                <?php else: ?>

                    <table class="cat-table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>تاريخ التسجيل</th>
                            <th>عدد الطلبات</th>
                            <th>إجراءات</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td><?php echo (int)$row['orders_count']; ?></td>
                                <td>
                                    <a href="delete_user.php?id=<?php echo $row['id']; ?>"
                                       class="table-link delete"
                                       onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');">
                                        حذف
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