<?php
require_once "../auth.php";

require_once "../../includes/config.php";

$errors  = [];
$success = null;

// جلب التصنيفات لاستخدامها في قائمة الاختيار
$categories = [];
$catSql  = "SELECT id, name FROM categories ORDER BY name";
$catStmt = mysqli_prepare($conn, $catSql);
mysqli_stmt_execute($catStmt);
$catResult = mysqli_stmt_get_result($catStmt);

while ($row = mysqli_fetch_assoc($catResult)) {
    $categories[] = $row; // كل عنصر فيه id + name
}
mysqli_stmt_close($catStmt);

// لو مافيش تصنيفات، مالهاش معنى نضيف منتجات
if (empty($categories)) {
    $errors[] = "لا يمكن إضافة منتج لأنّه لا يوجد أي تصنيفات. يرجى إضافة تصنيف أولاً.";
}

// 2) لو تم إرسال الفورم
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($categories)) {

    $name        = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price       = trim($_POST["price"] ?? "");
    $category_id = $_POST["category_id"] ?? "";

    $imageName = null;

    // التحقق من الاسم
    if ($name === "") {
        $errors[] = "من فضلك أدخل اسم المنتج.";
    }

    // التحقق من السعر
    if ($price === "") {
        $errors[] = "من فضلك أدخل سعر المنتج.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $errors[] = "السعر يجب أن يكون رقمًا أكبر من صفر.";
    }

    // التحقق من التصنيف
    if ($category_id === "" || !ctype_digit($category_id)) {
        $errors[] = "من فضلك اختر تصنيفًا صحيحًا.";
    }

    // معالجة رفع الصورة (اختياري)
    if (!empty($_FILES["image"]["name"])) {
        $allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
        $fileType     = $_FILES["image"]["type"];
        $fileError    = $_FILES["image"]["error"];

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "نوع الصورة غير مسموح. استخدم JPG أو PNG أو GIF أو WEBP.";
        } elseif ($fileError !== UPLOAD_ERR_OK) {
            $errors[] = "حدث خطأ أثناء رفع الصورة.";
        } else {
            $uploadDir = "../../uploads/products/";

            // إنشاء المجلد إذا مش موجود
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext       = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $imageName = time() . "_" . uniqid() . "." . $ext;
            $destPath  = $uploadDir . $imageName;

            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $destPath)) {
                $errors[] = "تعذر حفظ الصورة على السيرفر.";
                $imageName = null;
            }
        }
    }

    // لو ما في أخطاء → ندخل المنتج في قاعدة البيانات
    if (empty($errors)) {
        $sql = "INSERT INTO products (category_id, name, description, price, image)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "issds",
            $category_id,
            $name,
            $description,
            $price,
            $imageName
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            // بعد الإضافة الناجحة نرجع لصفحة قائمة المنتجات
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "حدث خطأ أثناء حفظ المنتج: " . mysqli_error($conn);
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة منتج جديد</title>
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
            <a href="#" class="nav-link">الطلبات</a>
            <a href="#" class="nav-link">المستخدمون</a>
        </nav>

        <a href="../logout.php" class="nav-link logout-link">تسجيل الخروج</a>
    </aside>

    <!-- محتوى الصفحة -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <div>
                <h1>إضافة منتج جديد</h1>
                <p>قم بإدخال بيانات المنتج ثم اضغط حفظ.</p>
            </div>
        </header>

        <section class="dashboard-cards">
            <div class="dash-card wide">

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $err): ?>
                            <div><?php echo htmlspecialchars($err); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="" enctype="multipart/form-data" class="cat-form">
                    <div class="form-group">
                        <label for="name">اسم المنتج</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?php echo htmlspecialchars($_POST['name'] ?? ""); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="category_id">التصنيف</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">اختر التصنيف</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"
                                    <?php
                                    if (!empty($_POST['category_id']) && $_POST['category_id'] == $cat['id']) {
                                        echo 'selected';
                                    }
                                    ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price">السعر</label>
                        <input
                            type="number"
                            step="0.01"
                            id="price"
                            name="price"
                            value="<?php echo htmlspecialchars($_POST['price'] ?? ""); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="description">وصف المنتج</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                        ><?php echo htmlspecialchars($_POST['description'] ?? ""); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">صورة المنتج (اختياري)</label>
                        <input
                            type="file"
                            id="image"
                            name="image"
                            accept="image/*"
                        >
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary"
                            <?php echo empty($categories) ? 'disabled' : ''; ?>>
                            حفظ المنتج
                        </button>
                        <a href="index.php" class="btn-secondary">رجوع إلى قائمة المنتجات</a>
                    </div>
                </form>

            </div>
        </section>
    </main>
</div>

</body>
</html>