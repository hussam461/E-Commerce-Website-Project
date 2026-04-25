<?php
require_once "../auth.php";

require_once "../../includes/config.php";

$errors   = [];
$category = null; 

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) { //!ctype_digite رقم غير صحيح
    header("Location: index.php");
    exit;
}

$category_id = (int) $_GET['id'];

$sql  = "SELECT id, name, image FROM categories WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$result   = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// لو ما لقيناه نرجع للقائمة
if (!$category) {
    header("Location: index.php");
    exit;
}

// نخزّن اسم الصورة القديمة
$oldImage = $category['image'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name      = trim($_POST["name"] ?? "");
    $imageName = $oldImage; // افتراضيًا نخلي الصورة القديمة كما هي

    if ($name === "") {
        $errors[] = "من فضلك أدخل اسم التصنيف.";
    }

    // لو المستخدم اختار صورة جديدة
    if (!empty($_FILES["image"]["name"])) {
        $allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
        $fileType     = $_FILES["image"]["type"];
        $fileError    = $_FILES["image"]["error"];

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "نوع الصورة غير مسموح. استخدم JPG أو PNG أو GIF أو WEBP.";
        } elseif ($fileError !== UPLOAD_ERR_OK) {
            $errors[] = "حدث خطأ أثناء رفع الصورة.";
        } else {
            $uploadDir = "../../uploads/categories/";

            // نطلع الامتداد
            $ext       = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            // نسوي اسم جديد للصورة
            $imageName = time() . "_" . uniqid() . "." . $ext;
            $destPath  = $uploadDir . $imageName;

            // ننقل الصورة
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $destPath)) {
                $errors[] = "تعذر حفظ الصورة على السيرفر.";
                // لو فشل الرفع، نرجّع الصورة القديمة
                $imageName = $oldImage;
            } else {
                // لو في صورة قديمة، نحذفها (اختياري بس احترافي)
                if (!empty($oldImage)) {
                    $oldPath = $uploadDir . $oldImage;
                    if (is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }
        }
    }

    // لو ما في أخطاء  نحدّث التصنيف في قاعدة البيانات
    if (empty($errors)) {
        $sql  = "UPDATE categories SET name = ?, image = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $name, $imageName, $category_id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: index.php"); // نرجع للقائمة
            exit;
        } else {
            $errors[] = "حدث خطأ أثناء تعديل التصنيف: " . mysqli_error($conn);
            mysqli_stmt_close($stmt);
        }

        // نحدّث القيم في المصفوفة الحالية عشان لو صار خطأ نظل نشوف القيم الجديدة
        $category['name']  = $name;
        $category['image'] = $imageName;
        $oldImage          = $imageName;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل التصنيف</title>
    <link rel="stylesheet" href="../css/style.css?v=1.0">
</head>
<body class="dashboard-body">

<div class="dashboard-layout">

    <!-- الشريط الجانبي -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">A</div>
            <div>
                <h2>لوحة التحكم</h2>
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
            <h1>تعديل التصنيف</h1>
            <p>قم بتعديل بيانات التصنيف ثم اضغط حفظ.</p>
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
                            value="<?php echo htmlspecialchars($category['name']); ?>"
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
                        <?php if (!empty($category['image'])): ?>
                            <div class="current-image">
                                <p class="dash-note">الصورة الحالية:</p>
                                <img src="../../uploads/categories/<?php echo htmlspecialchars($category['image']); ?>"
                                     alt="صورة التصنيف" class="cat-image">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">حفظ التعديلات</button>
                        <a href="index.php" class="btn-secondary">رجوع إلى التصنيفات</a>
                    </div>
                </form>

            </div>
        </section>
    </main>

</div>

</body>
</html>