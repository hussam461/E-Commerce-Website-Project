<?php
// الاتصال بقاعدة البيانات
require_once  "includes/config.php";

// جلب التصنيفات
$catSql = "SELECT id, name, image FROM categories ORDER BY name";
$catStmt = mysqli_prepare($conn, $catSql);
mysqli_stmt_execute($catStmt);
$catResult = mysqli_stmt_get_result($catStmt);

// جلب بعض المنتجات (مثلاً آخر 8 منتجات)
$prodSql = "SELECT p.id, p.name, p.price, p.image, c.name AS category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            ORDER BY p.id DESC
            LIMIT 8";
$prodStmt = mysqli_prepare($conn, $prodSql);
mysqli_stmt_execute($prodStmt);
$prodResult = mysqli_stmt_get_result($prodStmt);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>متجري الإلكتروني</title>

    <link rel="stylesheet" href="frontend/css/style.css?v=1.0">
</head>
<body class="fe-body">

<header class="fe-header">
    <div class="fe-logo">
        <span class="fe-logo-icon">A</span>
        <span class="fe-logo-text">متجري الإلكتروني</span>
    </div>

    <nav class="fe-nav">
        <a href="index.php" class="fe-nav-link active">الرئيسية</a>
        <a href="product.php" class="fe-nav-link">المنتجات</a>
        <a href="cart.php" class="fe-nav-link">سلة المشتريات</a>
        <a href="login.php" class="fe-nav-link fe-nav-auth">تسجيل الدخول</a>
    </nav>
</header>

<main class="fe-main">

    <!-- قسم الترحيب -->
    <section class="fe-hero">
        <div>
            <h1>أهلاً بك في متجرك الإلكتروني</h1>
            <p>تسوق أفضل المنتجات بأسعار مميزة وبواجهة احترافية بسيطة.</p>
            <a href="product.php" class="fe-btn-primary">استعرض جميع المنتجات</a>
        </div>
    </section>

    <div class="fe-container">

        <!-- التصنيفات -->
        <section class="fe-section">
            <div class="fe-section-header">
                <h2>التصنيفات</h2>
                <p>اختر التصنيف الذي يناسبك.</p>
            </div>

            <?php if (mysqli_num_rows($catResult) == 0): ?>
                <p class="fe-note">لا يوجد أي تصنيف حتى الآن.</p>
            <?php else: ?>
                <div class="fe-categories-grid">
                    <?php while ($cat = mysqli_fetch_assoc($catResult)): ?>
                        <div class="fe-category-card">
                            <?php if (!empty($cat['image'])): ?>
                                <img src="uploads/categories/<?php echo htmlspecialchars($cat['image']); ?>" alt="تصنيف">
                            <?php endif; ?>
                            <div class="fe-category-name">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </section>

        
        <hr class="fe-divider">

        <!-- المنتجات -->
        <section class="fe-section">
            <div class="fe-section-header">
                <h2>أحدث المنتجات</h2>
                <p>آخر المنتجات المضافة إلى المتجر.</p>
            </div>

            <?php if (mysqli_num_rows($prodResult) == 0): ?>
                <p class="fe-note">لا يوجد أي منتج حتى الآن.</p>
            <?php else: ?>
                <div class="fe-products-grid">
                    <?php while ($prod = mysqli_fetch_assoc($prodResult)): ?>
                        <div class="fe-product-card">
                            <?php if (!empty($prod['image'])): ?>
                                <img src="uploads/products/<?php echo htmlspecialchars($prod['image']); ?>" alt="منتج">
                            <?php endif; ?>

                            <div class="fe-product-info">
                                <div class="fe-product-name">
                                    <?php echo htmlspecialchars($prod['name']); ?>
                                </div>
                                <div class="fe-product-category">
                                    التصنيف: <?php echo htmlspecialchars($prod['category_name']); ?>
                                </div>
                                <div class="fe-product-bottom">
                                    <span class="fe-product-price">
                                        <?php echo number_format($prod['price'], 2); ?> $
                                    </span>

                                    <a href="product_details.php?id=<?php echo $prod['id']; ?>" class="fe-btn-link">
                                        تفاصيل
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<footer class="fe-footer">
    &copy; <?php echo date('Y'); ?> متجري الإلكتروني - جميع الحقوق محفوظة.
</footer>

</body>
</html>