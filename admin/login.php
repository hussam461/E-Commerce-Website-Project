<?php
session_start();//ينشأ جلسة بين المتصفح والسيرفر

require_once "../includes/config.php";

$error = "";
//هل الفورم انرسل 
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");  //   ?? لو القيمة مش موجود خليها فاضية 
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "من فضلك أدخل اسم المستخدم وكلمة المرور.";
    } else {
        $sql  = "SELECT id, username, password FROM admins WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);  
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row["password"])) {//

                // تخزين بيانات الأدمن في السيشن
                $_SESSION["admin_id"]       = $row["id"];
                $_SESSION["admin_username"] = $row["username"];

                header("Location: dashboard.php");
                exit; //ويوقف تنفيذ الملف 

            } else {
                $error = "اسم المستخدم أو كلمة المرور غير صحيحة.";
            }
        } else {
            $error = "اسم المستخدم أو كلمة المرور غير صحيحة.";
        }

        mysqli_stmt_close($stmt);
    }
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل دخول الأدمن</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="logo-circle">A</div>
            
            <h1>لوحة تحكم المتجر</h1>
            <p>من فضلك سجّل دخولك لإدارة المتجر</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="login-form">
            <div class="form-group">
                <label for="username">اسم المستخدم</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="أدخل اسم المستخدم"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="أدخل كلمة المرور"
                    required
                >
            </div>

            <button type="submit" class="btn-primary">تسجيل الدخول</button>

            <p class="login-hint">
                هذه الصفحة مخصّصة لمدير النظام فقط.
            </p>
        </form>
    </div>

    <p class="login-footer">
        &copy; <?php echo date("Y"); ?> لوحة إدارة المتجر الإلكتروني
    </p>
</div>

</body>
</html>
