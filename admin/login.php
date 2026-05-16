<?php
require_once __DIR__ . '/../config/app.php';

if (isAdminLoggedIn()) {
    redirect(ADMIN_URL . '/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'يرجى إدخال اسم المستخدم وكلمة المرور';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE (username = ? OR email = ?) AND is_active = 1");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_role'] = $admin['role'];
            $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);
            redirect(ADMIN_URL . '/dashboard.php');
        } else {
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول المشرفين - دليل الصيدليات المصرية</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
</head>
<body>
<div class="auth-wrapper" style="background: linear-gradient(135deg, #1a1a2e, #16213e);">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-user-shield auth-icon" style="color:#1a1a2e;"></i>
            <h3>لوحة تحكم المشرفين</h3>
            <p>دليل الصيدليات المصرية</p>
        </div>
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">اسم المستخدم أو البريد الإلكتروني</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-dark w-100 py-2 fw-bold">
                <i class="fas fa-sign-in-alt ms-1"></i> دخول المشرفين
            </button>
        </form>
        <div class="text-center mt-3">
            <a href="<?php echo BASE_URL; ?>" class="text-muted" style="font-size:0.85rem;"><i class="fas fa-arrow-right ms-1"></i> العودة للموقع</a>
        </div>
    </div>
</div>
</body>
</html>
