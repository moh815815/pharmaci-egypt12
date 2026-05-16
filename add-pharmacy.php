<?php
require_once __DIR__ . '/config/app.php';

$page_title = 'إضافة صيدلية جديدة - دليل الصيدليات المصرية';
$show_header = true;
$breadcrumbs = [
    ['label' => 'الرئيسية', 'url' => BASE_URL],
    ['label' => 'إضافة صيدلية'],
];

$governorates = getGovernorates();
$tiers = getTiers();

$selected_tier = isset($_GET['tier']) ? sanitize($_GET['tier']) : 'basic';
$tier_data = getTierBySlug($selected_tier);
if (!$tier_data) $tier_data = $tiers[0];

// Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Require login to submit
    if (!isLoggedIn()) {
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'يرجى تسجيل الدخول أولاً لإضافة صيدلية'];
        redirect(BASE_URL . '/auth/login.php');
    }

    try {
        $pdo = getDB();

        $name_ar        = sanitize($_POST['name_ar'] ?? '');
        $owner_name     = sanitize($_POST['owner_name'] ?? '');
        $phone          = sanitize($_POST['phone'] ?? '');
        $whatsapp       = sanitize($_POST['whatsapp'] ?? '');
        $email          = sanitize($_POST['email'] ?? '');
        $governorate_id = (int)($_POST['governorate_id'] ?? 0);
        $city_id        = (int)($_POST['city_id'] ?? 0);
        $street_id      = (int)($_POST['street_id'] ?? 0);
        $building_no    = sanitize($_POST['building_no'] ?? '');
        $floor          = sanitize($_POST['floor'] ?? '');
        $landmark       = sanitize($_POST['landmark'] ?? '');
        $latitude       = sanitize($_POST['latitude'] ?? '');
        $longitude      = sanitize($_POST['longitude'] ?? '');
        $is_24hours     = isset($_POST['is_24hours']) ? 1 : 0;
        $has_delivery   = isset($_POST['has_delivery']) ? 1 : 0;
        $description    = sanitize($_POST['description'] ?? '');
        $tier_id        = (int)$tier_data['id'];

        // Validate
        if (empty($name_ar) || empty($phone) || !$governorate_id || !$city_id || !$street_id) {
            throw new Exception('يرجى ملء جميع الحقول المطلوبة');
        }

        // Upload logo
        $logo = '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $result = uploadFile($_FILES['logo'], PHOTOS_PATH);
            if ($result['success']) {
                $logo = $result['filename'];
            }
        }

        // Insert pharmacy
        $stmt = $pdo->prepare("
            INSERT INTO pharmacies (tier_id, governorate_id, city_id, street_id, user_id, name_ar, owner_name, phone, whatsapp, email, building_no, floor, landmark, latitude, longitude, logo, is_24hours, has_delivery, description, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $tier_id, $governorate_id, $city_id, $street_id, $_SESSION['user_id'],
            $name_ar, $owner_name, $phone, $whatsapp, $email,
            $building_no, $floor, $landmark, $latitude ?: null, $longitude ?: null,
            $logo ?: null, $is_24hours, $has_delivery, $description,
        ]);
        $pharmacy_id = $pdo->lastInsertId();

        // Upload photos (if tier allows)
        if ($tier_data['photos_limit'] > 0 && isset($_FILES['photos'])) {
            $uploaded_photos = uploadMultipleFiles($_FILES['photos'], PHOTOS_PATH);
            $order = 0;
            foreach ($uploaded_photos as $photo) {
                $stmt = $pdo->prepare("INSERT INTO pharmacy_photos (pharmacy_id, photo, sort_order) VALUES (?, ?, ?)");
                $stmt->execute([$pharmacy_id, $photo, $order]);
                $order++;
            }
        }

        // Upload license
        if (isset($_FILES['license']) && $_FILES['license']['error'] === UPLOAD_ERR_OK) {
            $result = uploadFile($_FILES['license'], LICENSES_PATH, ['pdf', 'jpg', 'jpeg', 'png']);
            if ($result['success']) {
                $stmt = $pdo->prepare("INSERT INTO pharmacy_licenses (pharmacy_id, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $pharmacy_id,
                    $_FILES['license']['name'],
                    $result['filename'],
                    strtolower(pathinfo($_FILES['license']['name'], PATHINFO_EXTENSION)),
                    $_FILES['license']['size'],
                ]);
                // Mark pharmacy as having license
                $pdo->prepare("UPDATE pharmacies SET has_license = 1, license_file = ? WHERE id = ?")
                   ->execute([$result['filename'], $pharmacy_id]);
            }
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم إضافة الصيدلية بنجاح! سيتم مراجعة طلبك من قبل الإدارة قريباً.'];
        redirect(BASE_URL . '/pharmacy.php?id=' . $pharmacy_id);

    } catch (Exception $e) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => $e->getMessage()];
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Selected Tier Info -->
            <div class="form-section text-center mb-4" data-aos="fade-up">
                <h4 class="fw-bold">
                    <i class="fas fa-plus-circle text-primary ms-1"></i>
                    إضافة صيدلية جديدة
                </h4>
                <div class="mt-3">
                    <span class="badge bg-primary px-3 py-2" style="font-size:0.9rem;">
                        الباقة المختارة: <?php _e($tier_data['name_ar']); ?>
                        <?php if ($tier_data['price'] > 0): ?>
                            - <?php echo number_format($tier_data['price']) . ' ' . CURRENCY_SYMBOL; ?> / سنوياً
                        <?php else: ?>
                            - مجانية
                        <?php endif; ?>
                    </span>
                    <a href="<?php echo BASE_URL; ?>/pricing.php" class="btn btn-sm btn-outline-primary ms-2">
                        <i class="fas fa-exchange-alt ms-1"></i> تغيير الباقة
                    </a>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" data-aos="fade-up">
                <!-- Basic Info -->
                <div class="form-section">
                    <h5 class="fw-bold mb-3"><i class="fas fa-info-circle text-primary ms-1"></i> المعلومات الأساسية</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">اسم الصيدلية <span class="text-danger">*</span></label>
                            <input type="text" name="name_ar" class="form-control" placeholder="مثال: صيدلية السلام" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">اسم المالك</label>
                            <input type="text" name="owner_name" class="form-control" placeholder="مثال: أحمد محمد">
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label class="form-label">رقم الهاتف <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" placeholder="01XXXXXXXXX" dir="ltr" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">رقم الواتساب</label>
                            <input type="text" name="whatsapp" class="form-control" placeholder="01XXXXXXXXX" dir="ltr">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" placeholder="example@email.com" dir="ltr">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">وصف الصيدلية</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="معلومات إضافية عن الصيدلية والخدمات المقدمة..."></textarea>
                    </div>
                </div>

                <!-- Location -->
                <div class="form-section">
                    <h5 class="fw-bold mb-3"><i class="fas fa-map-marker-alt text-primary ms-1"></i> الموقع الجغرافي</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">المحافظة <span class="text-danger">*</span></label>
                            <select name="governorate_id" class="form-select" required>
                                <option value="">-- اختر المحافظة --</option>
                                <?php foreach ($governorates as $gov): ?>
                                    <option value="<?php echo $gov['id']; ?>"><?php _e($gov['name_ar']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">المدينة / المنطقة <span class="text-danger">*</span></label>
                            <select name="city_id" class="form-select" disabled required>
                                <option value="">-- اختر المحافظة أولاً --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الشارع <span class="text-danger">*</span></label>
                            <select name="street_id" class="form-select" disabled required>
                                <option value="">-- اختر المدينة أولاً --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label class="form-label">رقم المبنى</label>
                            <input type="text" name="building_no" class="form-control" placeholder="مثال: 15">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الدور</label>
                            <input type="text" name="floor" class="form-control" placeholder="مثال: 3">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">أقرب معلم</label>
                            <input type="text" name="landmark" class="form-control" placeholder="مثال: بجوار مستشفى السلام">
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label class="form-label">خط العرض (Latitude)</label>
                            <input type="text" name="latitude" class="form-control" placeholder="مثال: 30.044420" dir="ltr">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">خط الطول (Longitude)</label>
                            <input type="text" name="longitude" class="form-control" placeholder="مثال: 31.235712" dir="ltr">
                        </div>
                    </div>
                    <div class="mt-2 text-muted" style="font-size:0.82rem;">
                        <i class="fas fa-info-circle ms-1"></i> يمكنك الحصول على الإحداثيات من <a href="https://maps.google.com" target="_blank">خرائط جوجل</a>
                    </div>
                </div>

                <!-- Services -->
                <div class="form-section">
                    <h5 class="fw-bold mb-3"><i class="fas fa-concierge-bell text-primary ms-1"></i> الخدمات</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_24hours" value="1" id="chk24h">
                                <label class="form-check-label fw-bold" for="chk24h">
                                    <i class="fas fa-clock text-success ms-1"></i> صيدلية حراسة - تعمل 24 ساعة
                                </label>
                                <div class="text-muted" style="font-size:0.78rem;">اختر هذا إذا كانت الصيدلية تعمل على مدار الساعة</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="has_delivery" value="1" id="chkdel">
                                <label class="form-check-label fw-bold" for="chkdel">
                                    <i class="fas fa-truck text-primary ms-1"></i> خدمة التوصيل
                                </label>
                                <div class="text-muted" style="font-size:0.78rem;">اختر هذا إذا كانت الصيدلية توفر خدمة التوصيل للمنازل</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logo Upload -->
                <?php if ($tier_data['has_logo']): ?>
                <div class="form-section">
                    <h5 class="fw-bold mb-3"><i class="fas fa-image text-primary ms-1"></i> شعار الصيدلية</h5>
                    <div class="file-upload-wrapper" onclick="document.getElementById('pharmacyLogo').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>اضغط لرفع شعار الصيدلية</p>
                        <span class="file-info">jpg, png, webp - الحد الأقصى 5 ميجابايت</span>
                    </div>
                    <input type="file" name="logo" id="pharmacyLogo" accept="image/jpeg,image/png,image/webp" class="d-none">
                    <div id="logoPreview" class="text-center mt-3"></div>
                </div>
                <?php endif; ?>

                <!-- Photos Upload -->
                <?php if ($tier_data['photos_limit'] > 0): ?>
                <div class="form-section">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-images text-primary ms-1"></i>
                        صور الصيدلية
                        <span class="badge bg-info">الحد الأقصى: <?php echo $tier_data['photos_limit']; ?> صور</span>
                    </h5>
                    <div class="file-upload-wrapper" onclick="document.getElementById('pharmacyPhotos').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>اضغط لرفع صور الصيدلية</p>
                        <span class="file-info">jpg, png, webp - الحد الأقصى 5 ميجابايت لكل صورة</span>
                    </div>
                    <input type="file" name="photos[]" id="pharmacyPhotos" accept="image/jpeg,image/png,image/webp" multiple class="d-none">
                    <div id="photoCount" class="mt-2 text-muted" style="font-size:0.85rem;">0 صور مختارة</div>
                    <div id="photoPreview" class="file-preview"></div>
                </div>
                <?php endif; ?>

                <!-- License Upload -->
                <div class="form-section">
                    <h5 class="fw-bold mb-3"><i class="fas fa-file-contract text-primary ms-1"></i> رخصة الصيدلية <span class="text-muted" style="font-size:0.78rem;">(اختياري - يزيد من مصداقيتك)</span></h5>
                    <div class="file-upload-wrapper" onclick="document.getElementById('licenseFile').click()">
                        <i class="fas fa-file-pdf"></i>
                        <p>اضغط لرفع ترخيص الصيدلية</p>
                        <span class="file-info">PDF, jpg, png - الحد الأقصى 5 ميجابايت</span>
                    </div>
                    <input type="file" name="license" id="licenseFile" accept=".pdf,image/jpeg,image/png" class="d-none">
                    <div id="licenseName" class="mt-2 text-muted" style="font-size:0.85rem;">لم يتم اختيار ملف</div>
                </div>

                <!-- Submit -->
                <div class="text-center mt-4">
                    <?php if (!isLoggedIn()): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle ms-1"></i>
                            يجب <a href="<?php echo BASE_URL; ?>/auth/login.php">تسجيل الدخول</a> أو <a href="<?php echo BASE_URL; ?>/auth/register.php">إنشاء حساب</a> أولاً لإضافة صيدلية.
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="btn-submit px-5 py-3" <?php echo !isLoggedIn() ? 'disabled' : ''; ?>>
                        <i class="fas fa-check-circle ms-1"></i>
                        تأكيد وإضافة الصيدلية
                    </button>
                    <p class="text-muted mt-2" style="font-size:0.8rem;">
                        <i class="fas fa-shield-alt ms-1"></i>
                        سيتم مراجعة طلبك من قبل الإدارة قبل النشر
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
