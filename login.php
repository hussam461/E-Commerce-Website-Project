<?php
session_start();
require_once "includes/config.php";

$errors = [];
$email  = "";

// لو المستخدم أصلاً مسجّل دخول، ما يحتاج يشوف صفحة الدخول
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if ($email === "") {
        $errors[] = "البريد الإلكتروني مطلوب.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "البريد الإلكتروني غير صالح.";
    }

    if ($password === "") {
        $errors[] = "كلمة المرور مطلوبة.";
    }

    // لو ما في أخطاء نبدأ نتحقق من قاعدة البيانات
    if (empty($errors)) {

        $sql  = "SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // لو وجدنا مستخدم بهذا البريد
        if ($user && password_verify($password, $user['password'])) {

            // تخزين بيانات المستخدم في الجلسة
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            // لو عندنا صفحة كان رايح لها (مثلاً checkout) نرجعه لها
            $redirect = $_SESSION['redirect_after_login'] ?? null;
            if ($redirect) {
                unset($_SESSION['redirect_after_login']);
                header("Location: " . $redirect);
                exit;
            }

            // وإلا يرجع للرئيسية
            header("Location: index.php");
            exit;

        } else {
            $errors[] = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
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
        <a href="login.php" class="fe-nav-link fe-nav-auth active">تسجيل الدخول</a>
    </nav>
</header>

<main class="fe-main">
    <div class="fe-container">

        <section class="fe-section">
            <div class="fe-section-header">
                <h2>تسجيل الدخول</h2>
                <p>أدخل بريدك الإلكتروني وكلمة المرور للدخول إلى حسابك.</p>
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
                    <label>البريد الإلكتروني:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">

                    <label>كلمة المرور:</label>
                    <input type="password" name="password">

                    <button type="submit" class="fe-btn-primary">دخول</button>
                </form>

                <p style="margin-top:10px;font-size:0.9rem;color:#9ca3af;">
                    ليس لديك حساب؟ 
                    <a href="register.php" class="fe-btn-link">إنشاء حساب جديد</a>
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