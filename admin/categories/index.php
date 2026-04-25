<?php
require_once "../auth.php";
require_once "../../includes/config.php";

// استقبال رسالة الخطأ من الحذف
$errorMsg = $_GET['error'] ?? null;

// جلب التصنيفات
$sql = "SELECT id, name, image, created_at FROM categories ORDER BY id DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة التصنيفات</title>
    <link rel="stylesheet" href="../css/style.css?v=1.1">
</head>

<body class="dashboard-body">

<div class="dashboard-layout">

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">A</div>
            <div>
                <h2>لوحة التحكم</h2>
                <p><?php echo ($_SESSION['admin_username']); ?></p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="../dashboard.php" class="nav-link">الرئيسية</a>
            <a href="index.php" class="nav-link active">التصنيفات</a>
            <a href="../products/index.php" class="nav-link">المنتجات</a>
            <a href="../orders/index.php" class="nav-link">الطلبات</a>
            <a href="../users/index.php" class="nav-link">المستخدمون</a>
        </nav>

        <a href="../logout.php" class="nav-link logout-link">تسجيل الخروج</a>
    </aside>

    
    <!-- محتوى الصفحة -->
    <main class="dashboard-main">

        <header class="dashboard-header">
            <h1>إدارة التصنيفات</h1>
            <p>من هنا يمكنك استعراض وإدارة التصنيفات الموجودة في المتجر.</p>
        </header>

        <section class="dashboard-cards">
            <div class="dash-card wide">

                <div class="cat-header">
                    <h3>قائمة التصنيفات</h3>
                    <a href="add_category.php" class="btn-primary small-btn">إضافة تصنيف جديد </a>
                </div>

                <!-- رسالة خطأ تظهر فوق الجدول -->
                <?php if ($errorMsg): ?>
                    <div class="alert alert-error" style="margin-bottom: 10px;">
                        <?php echo htmlspecialchars($errorMsg); ?>
                    </div>
                <?php endif; ?>

                <?php if (mysqli_num_rows($result) == 0): ?>

                    <p class="dash-note">لا يوجد أي تصنيف حتى الآن.</p>

                <?php else: ?>

                    <table class="cat-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الصورة</th>
                                <th>الاسم</th>
                                <th>تاريخ الإضافة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>

                                <td>
                                    <?php if (!empty($row['image'])): ?>
                                        <img src="../../uploads/categories/<?php echo htmlspecialchars($row['image']); ?>"
                                             class="cat-image" alt="صورة التصنيف">
                                    <?php else: ?>
                                        <span class="dash-note">لا يوجد صورة</span>
                                    <?php endif; ?>
                                </td>

                                <td><?php echo htmlspecialchars($row['name']); ?></td>

                                <td><?php echo $row['created_at']; ?></td>

                                <td>
                                    <a href="edit_category.php?id=<?php echo $row['id']; ?>" class="table-link">تعديل</a>

                                    <a href="delete_category.php?id=<?php echo $row['id']; ?>"
                                    class="table-link delete"
                                       onclick="return confirm('هل أنت متأكد من حذف هذا التصنيف؟');">
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