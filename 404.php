<?php
require_once __DIR__ . '/config/app.php';

http_response_code(404);
$page_title = 'الصفحة غير موجودة - دليل الصيدليات المصرية';
$meta_description = 'عذراً، الصفحة التي تبحث عنها غير موجودة.';

include __DIR__ . '/includes/header.php';
?>

<div class="error-page">
    <div class="error-code">404</div>
    <h2>عذراً! الصفحة غير موجودة</h2>
    <p class="text-muted mb-4">الصفحة التي تبحث عنها قد تكون تم إزالتها أو أن الرابط غير صحيح.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-lg px-4"><i class="fas fa-home ms-1"></i> العودة للرئيسية</a>
        <a href="<?php echo BASE_URL; ?>/search.php" class="btn btn-outline-primary btn-lg px-4"><i class="fas fa-search ms-1"></i> بحث عن صيدلية</a>
        <a href="<?php echo BASE_URL; ?>/contact.php" class="btn btn-outline-secondary btn-lg px-4"><i class="fas fa-envelope ms-1"></i> اتصل بنا</a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
