<?php
require_once "../auth.php";
require_once "../../includes/config.php";

$errors  = [];

// معالجة الفورم عند الإرسال
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");//
    $imageName = null;

    // التحقق من اسم التصنيف
    if ($name === "") {
        $errors[] = "من فضلك أدخل اسم التصنيف.";
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
            // تأكد أن المجلد موجود: ../../uploads/categories
            $uploadDir = "../../uploads/categories/";


            $ext       = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);//يعطينا الامتداد الحقيقي للملف
            $imageName = time() . "_" . uniqid() . "." . $ext; //الاسم هنا بيكون فريد ولا يمكن يتكرر ابدا
            $destPath  = $uploadDir . $imageName;

            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $destPath)) {//تنقل الملف من المكان المؤقت الى المكان النهائي
                $errors[] = "تعذر حفظ الصورة على السيرفر.";
            }
        }
    }

    // لو ما في أخطاء: نحفظ في قاعدة البيانات
    if (empty($errors)) {
        $sql  = "INSERT INTO categories (name, image) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $name, $imageName); //2 parametr is string

        if (mysqli_stmt_execute($stmt)) {
            // بعد الإضافة الناجحة، نرجع لصفحة التصنيفات
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "حدث خطأ أثناء حفظ التصنيف: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة تصنيف جديد</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="dashboard-body">

<div class="dashboard-layout">

    <!-- الشريط الجانبي -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">A</div>
            <div>
                <h2>لوحة التجكم</h2>
                <p><?php echo htmlspecialchars($_SESSION["admin_username"]); ?></p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="../dashboard.php" class="nav-link">الرئيسية</a>
            <a href="index.php" class="nav-link active">التصنيفات</a>
            <a href="#" class="nav-link">المنتجات</a>
            <a href="#" class="nav-link">الطلبات</a>
            <a href="#" class="nav-link">المستخدمون</a>
        </nav>

        <a href="../logout.php" class="nav-link logout-link">تسجيل الخروج</a>
    </aside>

    <!-- محتوى الصفحة -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <h1>إضافة تصنيف جديد</h1>
            <p>أدخل بيانات التصنيف الجديد ثم اضغط حفظ.</p>
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
                        <label for="name">اسم التصنيف</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"
                            placeholder="مثال: ملابس رجالية"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="image">صورة التصنيف (اختياري)</label>
                        <input
                            type="file"
                            id="image"
                            name="image"
                            accept="image/*"
                        >
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">حفظ التصنيف</button>
                        <a href="index.php" class="btn-secondary">رجوع إلى التصنيفات</a>
                    </div>
                </form>

            </div>
        </section>
    </main>

</div>

</body>
</html>