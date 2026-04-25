<?php
require_once  "includes/config.php";

// جلب كل التصنيفات لعرضها في الفلتر
$catSql = "SELECT id, name FROM categories ORDER BY name";
$catStmt = mysqli_prepare($conn, $catSql);
mysqli_stmt_execute($catStmt);
$catResult = mysqli_stmt_get_result($catStmt);

// قراءة التصنيف المختار من الرابط (إن وجد)
$selectedCatId = isset($_GET['cat_id']) ? $_GET['cat_id'] : null;
if($selectedCatId ===''){
    $selectedCatId=null;
}
$selectedCatName = null;//بنخزن فيه اسم التصنيف المختار

$productsResult = null;

if ($selectedCatId !== null && ctype_digit($selectedCatId)) {
    $catId = (int)$selectedCatId;

    // نتأكد أن التصنيف موجود فعلاً
    $oneCatSql = "SELECT name FROM categories WHERE id = ? LIMIT 1";
    $oneCatStmt = mysqli_prepare($conn, $oneCatSql);
    mysqli_stmt_bind_param($oneCatStmt, "i", $catId);
    mysqli_stmt_execute($oneCatStmt);
    $oneCatRes = mysqli_stmt_get_result($oneCatStmt);
    $oneCatRow = mysqli_fetch_assoc($oneCatRes);
    mysqli_stmt_close($oneCatStmt);

    if ($oneCatRow) {
        $selectedCatName = $oneCatRow['name'];

        // جلب المنتجات الخاصة بهذا التصنيف فقط
        $prodSql = "SELECT p.id, p.name, p.price, p.image, c.name AS category_name
                    FROM products p
                    JOIN categories c ON p.category_id = c.id
                    WHERE p.category_id = ?
                    ORDER BY p.id DESC";
        $prodStmt = mysqli_prepare($conn, $prodSql);
        mysqli_stmt_bind_param($prodStmt, "i", $catId);
        mysqli_stmt_execute($prodStmt);
        $productsResult = mysqli_stmt_get_result($prodStmt);
        mysqli_stmt_close($prodStmt);
    } else {
        // لو التصنيف غير موجود، نعرض كل المنتجات بدون فلتر
        $selectedCatId = null;
    }
}

if ($selectedCatId === null) {
    // جلب كل المنتجات بدون فلتر
    $prodSql = "SELECT p.id, p.name, p.price, p.image, c.name AS category_name
                FROM products p
                JOIN categories c ON p.category_id = c.id
                ORDER BY p.id DESC";
    $prodStmt = mysqli_prepare($conn, $prodSql);
    mysqli_stmt_execute($prodStmt);
    $productsResult = mysqli_stmt_get_result($prodStmt);
    mysqli_stmt_close($prodStmt);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>جميع المنتجات</title>
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
        <a href="product.php" class="fe-nav-link active">المنتجات</a>
        <a href="cart.php" class="fe-nav-link">سلة المشتريات</a>
        <a href="login.php" class="fe-nav-link fe-nav-auth">تسجيل الدخول</a>
    </nav>
</header>

<main class="fe-main">
    <div class="fe-container">

        <section class="fe-section">
            <div class="fe-section-header">
                <h2>المنتجات</h2>
                <?php if ($selectedCatName): ?>
                    <p>عرض المنتجات في تصنيف: <strong><?php echo htmlspecialchars($selectedCatName); ?></strong></p>
                <?php else: ?>
                    <p>عرض جميع المنتجات المتاحة في المتجر.</p>
                <?php endif; ?>
            </div>

            <!-- فلتر التصنيف -->
            <form method="get" action="product.php" class="fe-filter-bar">
                <label for="cat_id">تصنيف المنتجات:</label>
                <select name="cat_id" id="cat_id" onchange="this.form.submit()">            <!--  اذا المستخدم غير الخيار في القائمة ارسل الفور ممباشرة -->

                    <option value="">كل التصنيفات</option>
                    <?php while ($cat = mysqli_fetch_assoc($catResult)): ?>
                        <option value="<?php echo $cat['id']; ?>"
                            <?php if ($selectedCatId == $cat['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
            <?php if (mysqli_num_rows($productsResult) == 0): ?>
                <p class="fe-note">لا يوجد منتجات لعرضها.</p>
            <?php else: ?>
                <div class="fe-products-grid">
                    <?php while ($prod = mysqli_fetch_assoc($productsResult)): ?>
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