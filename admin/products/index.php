<?php
// حماية الصفحة
require_once "../auth.php";

// الاتصال بقاعدة البيانات
require_once "../../includes/config.php";

// جلب المنتجات مع اسم التصنيف
$sql = "SELECT 
            p.id, 
            p.name, 
            p.price, 
            p.image, 
            p.created_at,
            c.name AS category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة المنتجات</title>
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
            <a href="index.php" class="nav-link active">المنتجات</a>
            <a href="../orders/index.php" class="nav-link">الطلبات</a>
            <a href="../users/index.php" class="nav-link">المستخدمون</a>
        </nav>

        <a href="../logout.php" class="nav-link logout-link">تسجيل الخروج</a>
    </aside>
    <!-- محتوى الصفحة -->
    <main class="dashboard-main">

        <header class="dashboard-header">
            <h1>إدارة المنتجات</h1>
            <p>من هنا يمكنك استعراض وإدارة المنتجات الموجودة في المتجر.</p>
        </header>

        <section class="dashboard-cards">
            <div class="dash-card wide">
                <div class="cat-header">
                <h3 >قائمة المنتجات</h3>
                 <a href="add_product.php" class="btn-primary small-btn"> إضافة منتج جديد</a>
                </div>

                <table class="cat-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الصورة</th>
                            <th>اسم المنتج</th>
                            <th>التصنيف</th>
                            <th>السعر</th>
                            <th>تاريخ الإضافة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>

                                <td><?php echo $row['id']; ?></td>

                                <td>
                                    <?php if (!empty($row['image'])): ?>
                                        <img src="../../uploads/products/<?php echo htmlspecialchars($row['image']); ?>"
                                             class="cat-image"
                                             alt="صورة المنتج">
                                    <?php else: ?>
                                        <span class="dash-note">لا توجد صورة</span>
                                    <?php endif; ?>
                                </td>

                                <td><?php echo htmlspecialchars($row['name']); ?></td>

                                <td><?php echo htmlspecialchars($row['category_name']); ?></td>

                                <td><?php echo number_format($row['price'], 2); ?> $</td>

                                <td><?php echo $row['created_at']; ?></td>

                                <td>
                                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="table-link">تعديل</a>
                                    <a href="delete_product.php?id=<?php echo $row['id']; ?>"
                                       class="table-link delete"
                                       onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟');">
                                        حذف
                                    </a>
                                </td>

                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="text-center dash-note">
                                لا يوجد أي منتجات حتى الآن
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

</body>
</html>