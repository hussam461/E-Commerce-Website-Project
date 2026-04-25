<?php
require_once "../auth.php";
require_once "../../includes/config.php";

$errors = [];

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = (int) $_GET['id'];

//  جلب قائمة التصنيفات لاستخدامها في الـ <select>
$categories = [];
$catSql  = "SELECT id, name FROM categories ORDER BY name";
$catStmt = mysqli_prepare($conn, $catSql);
mysqli_stmt_execute($catStmt);
$catResult = mysqli_stmt_get_result($catStmt);
while ($row = mysqli_fetch_assoc($catResult)) {
    $categories[] = $row;
}
mysqli_stmt_close($catStmt);

//  جلب بيانات المنتج الحالي
$sql  = "SELECT id, category_id, name, description, price, image 
         FROM products 
         WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result  = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$product) {
    // لو المنتج غير موجود نرجع لقائمة المنتجات
    header("Location: index.php");
    exit;
}

// قيم افتراضية لعرضها في الفورم
$name         = $product['name'];
$description  = $product['description'];
$price        = $product['price'];
$current_cat  = $product['category_id'];
$current_img  = $product['image'];

//  معالجة التعديل عند إرسال الفورم
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name        = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price       = trim($_POST["price"] ?? "");
    $current_cat = $_POST["category_id"] ?? "";

    $newImageName = $current_img; // افتراضياً نستخدم الصورة القديمة

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
    if ($current_cat === "" || !ctype_digit($current_cat)) {
        $errors[] = "من فضلك اختر تصنيفًا صحيحًا.";
    }

    // معالجة رفع صورة جديدة (اختياري)
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

            $ext         = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $newImageName = time() . "_" . uniqid() . "." . $ext;
            $destPath    = $uploadDir . $newImageName;

            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $destPath)) {
                $errors[] = "تعذر حفظ الصورة الجديدة على السيرفر.";
                $newImageName = $current_img; // نرجع للصورة القديمة
            } else {
                // لو فيه صورة قديمة نحذفها
                if (!empty($current_img)) {
                    $oldPath = $uploadDir . $current_img;
                    if (is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }
        }
    }

    // لو ما في أخطاء  نحدّث المنتج
    if (empty($errors)) {

        $sql = "UPDATE products 
                SET category_id = ?, 
                    name = ?, 
                    description = ?, 
                    price = ?, 
                    image = ?
                WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
        // i = category_id, s = name, s = desc, d = price, s = image, i = id
        mysqli_stmt_bind_param(
            $stmt,
            "issdsi",
            $current_cat,
            $name,
            $description,
            $price,
            $newImageName,
            $product_id
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "حدث خطأ أثناء تحديث بيانات المنتج: " . mysqli_error($conn);
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل المنتج</title>
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
                <h1>تعديل المنتج</h1>
                <p>قم بتعديل بيانات المنتج ثم اضغط حفظ.</p>
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
                            value="<?php echo htmlspecialchars($name); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="category_id">التصنيف</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">اختر التصنيف</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"
                                    <?php if ($current_cat == $cat['id']) echo 'selected'; ?>>
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
                            value="<?php echo htmlspecialchars($price); ?>"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label for="description">وصف المنتج</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                        ><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>الصورة الحالية</label>
                        <?php if (!empty($current_img)): ?>
                            <div>
                                <img src="../../uploads/products/<?php echo htmlspecialchars($current_img); ?>"
                                     class="cat-image"
                                     alt="الصورة الحالية للمنتج">
                            </div>
                        <?php else: ?>
                            <p class="dash-note">لا توجد صورة حاليًا.</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="image">صورة جديدة (اختياري)</label>
                        <input
                            type="file"
                            id="image"
                            name="image"
                            accept="image/*"
                        >
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">حفظ التعديلات</button>
                        <a href="index.php" class="btn-secondary">رجوع إلى قائمة المنتجات</a>
                    </div>

                </form>

            </div>
        </section>
    </main>
</div>

</body>
</html>