<?php
require_once __DIR__ . '/config/app.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    redirect(BASE_URL);
}

$pharmacy = getPharmacy($id);
if (!$pharmacy || $pharmacy['status'] != 'approved') {
    $page_title = 'الصيدلية غير موجودة';
    $show_header = true;
    $breadcrumbs = [
        ['label' => 'الرئيسية', 'url' => BASE_URL],
        ['label' => 'صيدلية غير موجودة'],
    ];
    include __DIR__ . '/includes/header.php';
    echo '<div class="container py-5 text-center"><h2>الصيدلية غير موجودة</h2><p class="text-muted">عذراً، الصيدلية التي تبحث عنها غير موجودة أو تم إزالتها.</p><a href="' . BASE_URL . '" class="btn btn-primary">العودة للرئيسية</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Increment views
$pdo = getDB();
$pdo->prepare("UPDATE pharmacies SET views_count = views_count + 1 WHERE id = ?")->execute([$id]);

$photos = getPharmacyPhotos($id);

$page_title = $pharmacy['name_ar'] . ' - دليل الصيدليات المصرية';
$show_header = true;
$breadcrumbs = [
    ['label' => 'الرئيسية', 'url' => BASE_URL],
    ['label' => $pharmacy['governorate_name'], 'url' => BASE_URL . '/search.php?governorate_id=' . $pharmacy['governorate_id']],
    ['label' => $pharmacy['name_ar']],
];

$whatsapp_number = $pharmacy['whatsapp'] ? preg_replace('/[^0-9]/', '', $pharmacy['whatsapp']) : '';

include __DIR__ . '/includes/header.php';
?>

<section class="pharmacy-detail py-4">
    <div class="container">
        <div class="row">
            <!-- Main Info -->
            <div class="col-lg-8">
                <div class="detail-header" data-aos="fade-up">
                    <div class="row align-items-start">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <h1 class="pharmacy-name mb-0"><?php _e($pharmacy['name_ar']); ?></h1>
                                <?php echo getTierBadge($pharmacy['tier_slug'], $pharmacy['tier_name']); ?>
                            </div>
                            <p class="text-muted"><?php _e($pharmacy['description']); ?></p>
                        </div>
                        <?php if ($pharmacy['logo']): ?>
                        <div class="col-md-4 text-center">
                            <img src="<?php echo UPLOADS_URL; ?>/photos/<?php echo $pharmacy['logo']; ?>" alt="<?php _e($pharmacy['name_ar']); ?>" class="img-fluid rounded shadow-sm" style="max-height:120px;">
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="pharmacy-meta">
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php _e($pharmacy['governorate_name'] . ' - ' . $pharmacy['city_name'] . ' - ' . $pharmacy['street_name']); ?>
                            <?php if ($pharmacy['building_no']): ?>، مبنى <?php _e($pharmacy['building_no']); ?><?php endif; ?>
                            <?php if ($pharmacy['floor']): ?>، دور <?php _e($pharmacy['floor']); ?><?php endif; ?>
                            <?php if ($pharmacy['landmark']): ?>، بجوار <?php _e($pharmacy['landmark']); ?><?php endif; ?>
                            </span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-phone-alt"></i>
                            <span dir="ltr"><?php _e($pharmacy['phone']); ?></span>
                        </div>
                        <?php if ($pharmacy['whatsapp']): ?>
                        <div class="meta-item">
                            <i class="fab fa-whatsapp text-success"></i>
                            <span dir="ltr"><?php _e($pharmacy['whatsapp']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($pharmacy['email']): ?>
                        <div class="meta-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php _e($pharmacy['email']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="meta-item">
                            <i class="fas fa-tag"></i>
                            <span>النوع: <?php echo getTierBadge($pharmacy['tier_slug'], $pharmacy['tier_name']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-eye"></i>
                            <span>عدد المشاهدات: <?php echo number_format($pharmacy['views_count']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>تاريخ الإضافة: <?php echo formatDate($pharmacy['created_at']); ?></span>
                        </div>
                        <?php if ($pharmacy['is_24hours']): ?>
                        <div class="meta-item">
                            <i class="fas fa-clock text-success"></i>
                            <span class="text-success fw-bold">يعمل 24 ساعة - صيدليات الحراسة</span>
                        </div>
                        <?php endif; ?>
                        <?php if ($pharmacy['has_delivery']): ?>
                        <div class="meta-item">
                            <i class="fas fa-truck text-primary"></i>
                            <span class="text-primary fw-bold">يوجد خدمة توصيل</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="action-buttons">
                        <a href="tel:<?php echo $pharmacy['phone']; ?>" class="btn btn-call">
                            <i class="fas fa-phone-alt ms-1"></i> اتصل الآن
                        </a>
                        <?php if ($whatsapp_number): ?>
                        <a href="https://wa.me/<?php echo $whatsapp_number; ?>" target="_blank" class="btn btn-whatsapp">
                            <i class="fab fa-whatsapp ms-1"></i> واتساب
                        </a>
                        <?php endif; ?>
                        <?php if ($pharmacy['latitude'] && $pharmacy['longitude']): ?>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $pharmacy['latitude']; ?>,<?php echo $pharmacy['longitude']; ?>" target="_blank" class="btn btn-directions">
                            <i class="fas fa-directions ms-1"></i> اتجاهات القيادة
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Photos Gallery -->
                <?php if (!empty($photos)): ?>
                <div class="detail-header" data-aos="fade-up">
                    <h4 class="fw-bold"><i class="fas fa-images text-primary ms-1"></i> معرض الصور</h4>
                    <div class="pharmacy-gallery">
                        <?php foreach ($photos as $photo): ?>
                        <div class="gallery-item" onclick="openGallery(this)">
                            <img src="<?php echo UPLOADS_URL; ?>/photos/<?php echo $photo['photo']; ?>" alt="<?php _e($pharmacy['name_ar']); ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Map -->
                <?php if ($pharmacy['latitude'] && $pharmacy['longitude']): ?>
                <div class="form-section" data-aos="fade-up">
                    <h5 class="fw-bold mb-3"><i class="fas fa-map-marked-alt text-primary ms-1"></i> الموقع على الخريطة</h5>
                    <div id="pharmacyMap" style="height:250px;border-radius:8px;overflow:hidden;"></div>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $pharmacy['latitude']; ?>,<?php echo $pharmacy['longitude']; ?>" target="_blank" class="btn btn-outline-primary w-100 mt-2">
                        <i class="fas fa-directions ms-1"></i> فتح في خرائط جوجل
                    </a>
                </div>
                <?php endif; ?>

                <!-- License Verified -->
                <?php if ($pharmacy['has_license'] && $pharmacy['license_verified']): ?>
                <div class="form-section text-center" data-aos="fade-up">
                    <i class="fas fa-shield-alt text-success" style="font-size:2.5rem;"></i>
                    <h6 class="fw-bold text-success mt-2">تم التحقق من الترخيص</h6>
                    <p class="text-muted mb-0" style="font-size:0.82rem;">هذه الصيدلية مرخصة وموثقة</p>
                </div>
                <?php endif; ?>

                <!-- Working Hours -->
                <div class="form-section" data-aos="fade-up">
                    <h5 class="fw-bold mb-3"><i class="fas fa-clock text-primary ms-1"></i> ساعات العمل</h5>
                    <?php if ($pharmacy['is_24hours']): ?>
                        <div class="text-center p-3 bg-success-light rounded">
                            <i class="fas fa-clock text-success fa-2x mb-2"></i>
                            <h6 class="fw-bold text-success">مفتوح 24 ساعة</h6>
                            <p class="text-muted mb-0">يومياً على مدار الساعة</p>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">غير محدد</p>
                    <?php endif; ?>
                </div>

                <!-- Share -->
                <div class="form-section" data-aos="fade-up">
                    <h5 class="fw-bold mb-3"><i class="fas fa-share-alt text-primary ms-1"></i> مشاركة</h5>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(BASE_URL . '/pharmacy.php?id=' . $pharmacy['id']); ?>" target="_blank" class="btn btn-outline-primary flex-fill"><i class="fab fa-facebook"></i></a>
                        <a href="https://wa.me/?text=<?php echo urlencode($pharmacy['name_ar'] . ' - ' . BASE_URL . '/pharmacy.php?id=' . $pharmacy['id']); ?>" target="_blank" class="btn btn-outline-success flex-fill"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($pharmacy['name_ar'] . ' - ' . BASE_URL . '/pharmacy.php?id=' . $pharmacy['id']); ?>" target="_blank" class="btn btn-outline-dark flex-fill"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($pharmacy['latitude'] && $pharmacy['longitude']): ?>
<script src="https://maps.googleapis.com/maps/api/js?key=&callback=initPharmacyMap" async defer></script>
<script>
function initPharmacyMap() {
    var lat = <?php echo $pharmacy['latitude']; ?>;
    var lng = <?php echo $pharmacy['longitude']; ?>;
    window.initPharmacyMap(lat, lng, '<?php echo addslashes($pharmacy['name_ar']); ?>');
}
</script>
<?php endif; ?>

<script>
function openGallery(el) {
    var img = el.querySelector('img');
    if (img) {
        // Could implement a lightbox here
        window.open(img.src, '_blank');
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
