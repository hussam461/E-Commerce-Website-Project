<?php
require_once "auth.php";

require_once "../includes/config.php";

//  عدد التصنيفات
$categoriesCount = 0;
$sql = "SELECT COUNT(*) AS cnt FROM categories";
$res = mysqli_query($conn, $sql);
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $categoriesCount = (int)$row['cnt'];
}

//  عدد المنتجات
$productsCount = 0;
$sql = "SELECT COUNT(*) AS cnt FROM products";
$res = mysqli_query($conn, $sql);
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $productsCount = (int)$row['cnt'];
}

//  عدد الطلبات
$ordersCount = 0;
$sql = "SELECT COUNT(*) AS cnt FROM orders";
$res = mysqli_query($conn, $sql);
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $ordersCount = (int)$row['cnt'];
}

//  عدد المستخدمين
$usersCount = 0;
$sql = "SELECT COUNT(*) AS cnt FROM users";
$res = mysqli_query($conn, $sql);
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $usersCount = (int)$row['cnt'];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم المتجر</title>
    <link rel="stylesheet" href="css/style.css?v=1.0">
</head>
<body class="dashboard-body">

<div class="dashboard-layout">

     <!-- الشريط الجانبي -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">A</div>
            <div>
                <h2>لوحة التحكم</h2>
                <p><?php echo ($_SESSION['admin_username']); ?></p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link active">الرئيسية</a>
            <a href="categories/index.php" class="nav-link">التصنيفات</a>
            <a href="products/index.php" class="nav-link">المنتجات</a>
            <a href="orders/index.php" class="nav-link">الطلبات</a>
            <a href="users/index.php" class="nav-link">المستخدمون</a>

            <div class="sidebar-separator"></div>

            <a href="logout.php" class="nav-link logout-link">تسجيل الخروج</a>
        </nav>
    </aside>

    <!-- محتوى الداشبورد -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <h1>مرحباً بك في لوحة تحكم المتجر</h1>
            <p>من هنا يمكنك إدارة التصنيفات، المنتجات، الطلبات والمستخدمين.</p>
        </header>

        <section class="dashboard-cards">

            <!-- عدد التصنيفات -->
            <div class="dash-card">
                <h3>عدد التصنيفات</h3>
                <p class="dash-card-number"><?php echo $categoriesCount; ?></p>
                <p class="dash-card-note">إدارة التصنيفات من قسم التصنيفات.</p>
            </div>

            <!-- عدد المنتجات -->
            <div class="dash-card">
                <h3>عدد المنتجات</h3>
                <p class="dash-card-number"><?php echo $productsCount; ?></p>
                <p class="dash-card-note">يمكنك إضافة وتعديل المنتجات من قسم المنتجات.</p>
            </div>

            <!-- عدد الطلبات -->
            <div class="dash-card">
                <h3>عدد الطلبات</h3>
                <p class="dash-card-number"><?php echo $ordersCount; ?></p>
                <p class="dash-card-note">استعرض الطلبات وغيّر حالتها من قسم الطلبات.</p>
            </div>

            <!-- عدد المستخدمين -->
            <div class="dash-card">
                <h3>عدد المستخدمين</h3>
                <p class="dash-card-number"><?php echo $usersCount; ?></p>
                <p class="dash-card-note">إدارة حسابات المستخدمين من قسم المستخدمين.</p>
            </div>

        </section>
    </main>
</div>

</body>
</html>