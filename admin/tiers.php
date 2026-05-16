<?php
require_once __DIR__ . '/../config/app.php';
$page_title = 'إدارة أنواع الاشتراكات';
include __DIR__ . '/includes/header.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $name_ar = sanitize($_POST['name_ar']);
    $price = (float)$_POST['price'];
    $photos_limit = (int)$_POST['photos_limit'];
    $has_logo = isset($_POST['has_logo']) ? 1 : 0;
    $has_whatsapp = isset($_POST['has_whatsapp']) ? 1 : 0;
    $has_map = isset($_POST['has_map']) ? 1 : 0;
    $has_license_upload = isset($_POST['has_license_upload']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $sort_order = (int)$_POST['sort_order'];

    $stmt = $pdo->prepare("UPDATE pharmacy_tiers SET name_ar=?, price=?, photos_limit=?, has_logo=?, has_whatsapp=?, has_map=?, has_license_upload=?, is_featured=?, sort_order=? WHERE id=?");
    $stmt->execute([$name_ar, $price, $photos_limit, $has_logo, $has_whatsapp, $has_map, $has_license_upload, $is_featured, $sort_order, $id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم تحديث الباقة'];
    redirect(ADMIN_URL . '/tiers.php');
}

$tiers = getTiers();
?>

<div class="admin-header">
    <h4><i class="fas fa-tags ms-1"></i> إدارة أنواع الاشتراكات (البطاقات)</h4>
</div>

<div class="row g-3">
    <?php foreach ($tiers as $tier): ?>
    <div class="col-md-4">
        <div class="form-section">
            <h5 class="fw-bold mb-3 text-center">
                <?php
                $icon = 'fa-id-card';
                if ($tier['slug'] == 'large') $icon = 'fa-crown text-warning';
                elseif ($tier['slug'] == 'medium') $icon = 'fa-star text-secondary';
                ?>
                <i class="fas <?php echo $icon; ?> ms-1"></i>
                <?php _e($tier['name_ar']); ?>
                <span class="badge bg-<?php echo $tier['slug'] == 'large' ? 'warning text-dark' : ($tier['slug'] == 'medium' ? 'secondary' : 'light text-dark'); ?>">
                    <?php echo $tier['price'] > 0 ? number_format($tier['price']) . ' ج.م' : 'مجاني'; ?>
                </span>
            </h5>
            <form method="POST">
                <input type="hidden" name="update" value="1">
                <input type="hidden" name="id" value="<?php echo $tier['id']; ?>">
                <div class="mb-2">
                    <label class="form-label" style="font-size:0.82rem;">الاسم</label>
                    <input type="text" name="name_ar" class="form-control form-control-sm" value="<?php _e($tier['name_ar']); ?>">
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:0.82rem;">السعر (ج.م)</label>
                        <input type="number" name="price" class="form-control form-control-sm" value="<?php echo $tier['price']; ?>" step="0.01">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:0.82rem;">عدد الصور</label>
                        <input type="number" name="photos_limit" class="form-control form-control-sm" value="<?php echo $tier['photos_limit']; ?>">
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:0.82rem;">الترتيب</label>
                        <input type="number" name="sort_order" class="form-control form-control-sm" value="<?php echo $tier['sort_order']; ?>">
                    </div>
                </div>
                <div class="mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="has_logo" value="1" id="logo<?php echo $tier['id']; ?>" <?php echo $tier['has_logo'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" style="font-size:0.82rem;" for="logo<?php echo $tier['id']; ?>">شعار الصيدلية</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="has_whatsapp" value="1" id="wa<?php echo $tier['id']; ?>" <?php echo $tier['has_whatsapp'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" style="font-size:0.82rem;" for="wa<?php echo $tier['id']; ?>">زر واتساب</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="has_map" value="1" id="map<?php echo $tier['id']; ?>" <?php echo $tier['has_map'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" style="font-size:0.82rem;" for="map<?php echo $tier['id']; ?>">خريطة الموقع</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="has_license_upload" value="1" id="lic<?php echo $tier['id']; ?>" <?php echo $tier['has_license_upload'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" style="font-size:0.82rem;" for="lic<?php echo $tier['id']; ?>">رفع الترخيص</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="feat<?php echo $tier['id']; ?>" <?php echo $tier['is_featured'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" style="font-size:0.82rem;" for="feat<?php echo $tier['id']; ?>">ظهور مميز</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-save ms-1"></i> حفظ التغييرات</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
