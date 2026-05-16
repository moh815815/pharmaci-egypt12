<?php
require_once __DIR__ . '/../config/app.php';
$page_title = 'إعدادات الموقع';
include __DIR__ . '/includes/header.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed_keys = ['site_name', 'site_description', 'admin_email', 'phone', 'whatsapp', 'address', 'facebook_url', 'twitter_url', 'instagram_url', 'pharmacies_per_page', 'google_maps_api_key'];
    foreach ($_POST as $key => $value) {
        if (in_array($key, $allowed_keys)) {
            $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");
            $stmt->execute([$key, sanitize($value)]);
        }
    }
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم حفظ الإعدادات بنجاح'];
    redirect(ADMIN_URL . '/settings.php');
}

// Get all settings
$settings = [];
$stmt = $pdo->query("SELECT * FROM settings");
foreach ($stmt->fetchAll() as $row) {
    $settings[$row['key_name']] = $row['value'];
}
?>

<div class="admin-header">
    <h4><i class="fas fa-cog ms-1"></i> إعدادات الموقع</h4>
</div>

<div class="form-section">
    <form method="POST">
        <h5 class="fw-bold mb-3"><i class="fas fa-globe ms-1"></i> المعلومات العامة</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">اسم الموقع</label>
                <input type="text" name="site_name" class="form-control" value="<?php _e($settings['site_name'] ?? 'دليل الصيدليات المصرية'); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">وصف الموقع</label>
                <input type="text" name="site_description" class="form-control" value="<?php _e($settings['site_description'] ?? ''); ?>">
            </div>
        </div>

        <h5 class="fw-bold mb-3"><i class="fas fa-phone ms-1"></i> معلومات الاتصال</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">رقم الهاتف</label>
                <input type="text" name="phone" class="form-control" value="<?php _e($settings['phone'] ?? ''); ?>" dir="ltr">
            </div>
            <div class="col-md-4">
                <label class="form-label">الواتساب</label>
                <input type="text" name="whatsapp" class="form-control" value="<?php _e($settings['whatsapp'] ?? ''); ?>" dir="ltr">
            </div>
            <div class="col-md-4">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="admin_email" class="form-control" value="<?php _e($settings['admin_email'] ?? ''); ?>" dir="ltr">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">العنوان</label>
            <input type="text" name="address" class="form-control" value="<?php _e($settings['address'] ?? ''); ?>">
        </div>

        <h5 class="fw-bold mb-3"><i class="fas fa-share-alt ms-1"></i> روابط التواصل الاجتماعي</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">فيسبوك</label>
                <input type="url" name="facebook_url" class="form-control" value="<?php _e($settings['facebook_url'] ?? ''); ?>" dir="ltr">
            </div>
            <div class="col-md-4">
                <label class="form-label">تويتر</label>
                <input type="url" name="twitter_url" class="form-control" value="<?php _e($settings['twitter_url'] ?? ''); ?>" dir="ltr">
            </div>
            <div class="col-md-4">
                <label class="form-label">انستجرام</label>
                <input type="url" name="instagram_url" class="form-control" value="<?php _e($settings['instagram_url'] ?? ''); ?>" dir="ltr">
            </div>
        </div>

        <h5 class="fw-bold mb-3"><i class="fas fa-cog ms-1"></i> إعدادات متقدمة</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">عدد الصيدليات في كل صفحة</label>
                <input type="number" name="pharmacies_per_page" class="form-control" value="<?php _e($settings['pharmacies_per_page'] ?? '12'); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">مفتاح Google Maps API</label>
                <input type="text" name="google_maps_api_key" class="form-control" value="<?php _e($settings['google_maps_api_key'] ?? ''); ?>" dir="ltr">
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg px-5"><i class="fas fa-save ms-1"></i> حفظ جميع الإعدادات</button>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
