<?php
session_start();
require_once "includes/config.php";

$errors  = [];
$name    = "";
$email   = "";
$password = "";

// لو المستخدم أصلاً مسجّل دخول، ما يحتاج يسوي حساب جديد
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // نقرأ بيانات الفورم
    $name     = trim($_POST['name'] ?? "");
    $email    = trim($_POST['email'] ?? "");
    $password = trim($_POST['password'] ?? "");

    //  التحقق من الاسم
    if ($name === "") {
        $errors[] = "الاسم مطلوب.";
    }

    if ($email === "") {
        $errors[] = "البريد الإلكتروني مطلوب.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "البريد الإلكتروني غير صالح.";
    }

    if ($password === "") {
        $errors[] = "كلمة المرور مطلوبة.";
    } elseif (strlen($password) < 6) {
        $errors[] = "يجب أن تكون كلمة المرور 6 أحرف على الأقل.";
    }

    if (empty($errors)) {

        //  نتأكد أن البريد غير مستخدم من قبل
        $checkSql  = "SELECT id FROM users WHERE email = ? LIMIT 1";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        $existing    = mysqli_fetch_assoc($checkResult);
        mysqli_stmt_close($checkStmt);

        if ($existing) {
            $errors[] = "هذا البريد الإلكتروني مستخدم مسبقاً.";
        } else {
            //  تشفير كلمة المرور
            $hashedPass = password_hash($password, PASSWORD_BCRYPT);

            //  إدخال المستخدم الجديد في جدول users
            $sql  = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashedPass);
            mysqli_stmt_execute($stmt);

            //  الحصول على id المستخدم الجديد
            $newUserId = mysqli_insert_id($conn);

            // 8) تسجيل دخوله مباشرة
            $_SESSION['user_id']   = $newUserId;
            $_SESSION['user_name'] = $name;

            //  هل عندنا صفحة كان رايح لها (مثلاً checkout)؟
            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);

            // 1 إعادة التوجيه
            header("Location: " . $redirect);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء حساب جديد</title>
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
        <a href="product.php" class="fe-nav-link">المنتجات</a>
        <a href="cart.php" class="fe-nav-link">السلة</a>
        <a href="login.php" class="fe-nav-link fe-nav-auth">تسجيل الدخول</a>
    </nav>
</header>

<main class="fe-main">
    <div class="fe-container">

        <section class="fe-section">
            <div class="fe-section-header">
                <h2>إنشاء حساب جديد</h2>
                <p>قم بملء البيانات بالأسفل لإنشاء حسابك.</p>
            </div>

            <div class="fe-form-box">

                <?php if (!empty($errors)): ?>
                    <div class="fe-alert-error">
                        <?php foreach ($errors as $e): ?>
                            <div><?php echo $e; ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="fe-form">
                    <label>الاسم:</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>">
                    <label>البريد الإلكتروني:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">

                    <label>كلمة المرور:</label>
                    <input type="password" name="password">

                    <button type="submit" class="fe-btn-primary">إنشاء الحساب</button>
                </form>

                <p style="margin-top:10px;font-size:0.9rem;color:#9ca3af;">
                    لديك حساب بالفعل؟
                    <a href="login.php" class="fe-btn-link">تسجيل الدخول</a>
                </p>

            </div>
        </section>

    </div>
</main>

<footer class="fe-footer">
    &copy; <?php echo date('Y'); ?> متجري الإلكتروني - جميع الحقوق محفوظة.
</footer>

</body>
</html>