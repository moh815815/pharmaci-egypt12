<?php
require_once __DIR__ . '/../config/app.php';

if (isLoggedIn()) {
    redirect(BASE_URL);
}

$page_title = 'تسجيل الدخول - دليل الصيدليات المصرية';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($phone) || empty($password)) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'يرجى إدخال رقم الهاتف وكلمة المرور'];
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ? AND is_active = 1");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'مرحباً بعودتك يا ' . $user['name']];
            redirect(BASE_URL);
        } else {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'رقم الهاتف أو كلمة المرور غير صحيحة'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e($page_title); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card" data-aos="fade-up">
        <div class="auth-header">
            <a href="<?php echo BASE_URL; ?>" class="text-decoration-none">
                <i class="fas fa-hospital-alt auth-icon"></i>
            </a>
            <h3>تسجيل الدخول</h3>
            <p>أدخل بياناتك للدخول إلى حسابك</p>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show">
            <?php echo $_SESSION['flash']['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">رقم الهاتف</label>
                <input type="text" name="phone" class="form-control" placeholder="01XXXXXXXXX" dir="ltr" required>
            </div>
            <div class="mb-3">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                <i class="fas fa-sign-in-alt ms-1"></i> دخول
            </button>
        </form>

        <div class="text-center mt-3">
            <p class="mb-1">ليس لديك حساب؟ <a href="<?php echo BASE_URL; ?>/auth/register.php">سجل الآن</a></p>
            <a href="<?php echo BASE_URL; ?>" class="text-muted" style="font-size:0.85rem;">
                <i class="fas fa-arrow-right ms-1"></i> العودة للرئيسية
            </a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
