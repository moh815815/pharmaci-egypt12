<?php
require_once __DIR__ . '/../config/app.php';

if (isLoggedIn()) {
    redirect(BASE_URL);
}

$page_title = 'إنشاء حساب جديد - دليل الصيدليات المصرية';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $whatsapp = sanitize($_POST['whatsapp'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($phone) || empty($password)) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'يرجى ملء جميع الحقول المطلوبة'];
    } elseif ($password !== $confirm_password) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'كلمتا المرور غير متطابقتين'];
    } elseif (mb_strlen($password) < 6) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل'];
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'رقم الهاتف مسجل بالفعل'];
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, whatsapp, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $whatsapp, $hashed]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم إنشاء الحساب بنجاح. يمكنك تسجيل الدخول الآن.'];
            redirect(BASE_URL . '/auth/login.php');
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
            <h3>إنشاء حساب جديد</h3>
            <p>سجل الآن وأضف صيدليتك إلى الدليل</p>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show">
            <?php echo $_SESSION['flash']['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">الاسم بالكامل <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="مثال: أحمد محمد" required>
            </div>
            <div class="mb-3">
                <label class="form-label">رقم الهاتف <span class="text-danger">*</span></label>
                <input type="text" name="phone" class="form-control" placeholder="01XXXXXXXXX" dir="ltr" required>
            </div>
            <div class="mb-3">
                <label class="form-label">رقم واتساب (اختياري)</label>
                <input type="text" name="whatsapp" class="form-control" placeholder="01XXXXXXXXX" dir="ltr">
            </div>
            <div class="mb-3">
                <label class="form-label">البريد الإلكتروني (اختياري)</label>
                <input type="email" name="email" class="form-control" placeholder="example@email.com" dir="ltr">
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">كلمة المرور <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required minlength="6">
                </div>
                <div class="col-md-6">
                    <label class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required minlength="6">
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                <i class="fas fa-user-plus ms-1"></i> إنشاء الحساب
            </button>
        </form>

        <div class="text-center mt-3">
            <p class="mb-1">لديك حساب بالفعل؟ <a href="<?php echo BASE_URL; ?>/auth/login.php">تسجيل دخول</a></p>
            <a href="<?php echo BASE_URL; ?>" class="text-muted" style="font-size:0.85rem;">
                <i class="fas fa-arrow-right ms-1"></i> العودة للرئيسية
            </a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
