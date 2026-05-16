<?php
require_once __DIR__ . '/config/app.php';

$page_title = 'بحث عن صيدليات - دليل الصيدليات المصرية';
$show_header = true;
$breadcrumbs = [
    ['label' => 'الرئيسية', 'url' => BASE_URL],
    ['label' => 'بحث عن صيدليات'],
];

$governorates = getGovernorates();
$results = [];
$search_params = [];

// Collect search params
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$governorate_id = isset($_GET['governorate_id']) ? (int)$_GET['governorate_id'] : 0;
$city_id = isset($_GET['city_id']) ? (int)$_GET['city_id'] : 0;
$street_id = isset($_GET['street_id']) ? (int)$_GET['street_id'] : 0;
$is_24hours = isset($_GET['is_24hours']) ? (int)$_GET['is_24hours'] : 0;
$has_delivery = isset($_GET['has_delivery']) ? (int)$_GET['has_delivery'] : 0;
$tier_id = isset($_GET['tier_id']) ? (int)$_GET['tier_id'] : 0;

$results = searchPharmacies([
    'search'        => $search,
    'governorate_id' => $governorate_id,
    'city_id'       => $city_id,
    'street_id'     => $street_id,
    'is_24hours'    => $is_24hours,
    'has_delivery'  => $has_delivery,
    'tier_id'       => $tier_id,
]);

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3" data-aos="fade-left">
            <div class="form-section" style="position:sticky;top:80px;">
                <h5 class="fw-bold mb-3"><i class="fas fa-filter ms-1"></i> فلترة البحث</h5>
                <form action="<?php echo BASE_URL; ?>/search.php" method="GET" id="searchForm">
                    <!-- Search -->
                    <div class="mb-3">
                        <label class="form-label">كلمة البحث</label>
                        <input type="text" name="search" class="form-control" placeholder="اسم الصيدلية أو الشارع..." value="<?php _e($search); ?>">
                    </div>

                    <!-- Governorate -->
                    <div class="mb-3">
                        <label class="form-label">المحافظة</label>
                        <select name="governorate_id" class="form-select">
                            <option value="">-- الكل --</option>
                            <?php foreach ($governorates as $gov): ?>
                                <option value="<?php echo $gov['id']; ?>" <?php echo $governorate_id == $gov['id'] ? 'selected' : ''; ?>>
                                    <?php _e($gov['name_ar']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- City -->
                    <div class="mb-3">
                        <label class="form-label">المدينة / المنطقة</label>
                        <select name="city_id" class="form-select" <?php echo $governorate_id ? '' : 'disabled'; ?>>
                            <option value="">-- الكل --</option>
                            <?php if ($governorate_id):
                                $cities = getCitiesByGovernorate($governorate_id);
                                foreach ($cities as $city): ?>
                                    <option value="<?php echo $city['id']; ?>" <?php echo $city_id == $city['id'] ? 'selected' : ''; ?>>
                                        <?php _e($city['name_ar']); ?>
                                    </option>
                                <?php endforeach;
                            endif; ?>
                        </select>
                    </div>

                    <!-- Street -->
                    <div class="mb-3">
                        <label class="form-label">الشارع</label>
                        <select name="street_id" class="form-select" <?php echo $city_id ? '' : 'disabled'; ?>>
                            <option value="">-- الكل --</option>
                            <?php if ($city_id):
                                $streets = getStreetsByCity($city_id);
                                foreach ($streets as $street): ?>
                                    <option value="<?php echo $street['id']; ?>" <?php echo $street_id == $street['id'] ? 'selected' : ''; ?>>
                                        <?php _e($street['name_ar']); ?>
                                    </option>
                                <?php endforeach;
                            endif; ?>
                        </select>
                    </div>

                    <!-- Type -->
                    <div class="mb-3">
                        <label class="form-label">نوع البطاقة</label>
                        <select name="tier_id" class="form-select">
                            <option value="">-- الكل --</option>
                            <?php $tiers = getTiers();
                            foreach ($tiers as $tier): ?>
                                <option value="<?php echo $tier['id']; ?>" <?php echo $tier_id == $tier['id'] ? 'selected' : ''; ?>>
                                    <?php _e($tier['name_ar']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Checkboxes -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_24hours" value="1" id="f24h" <?php echo $is_24hours ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="f24h"><i class="fas fa-clock text-primary ms-1"></i> صيدليات 24 ساعة</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="has_delivery" value="1" id="fdel" <?php echo $has_delivery ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="fdel"><i class="fas fa-truck text-success ms-1"></i> خدمة التوصيل</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search ms-1"></i> بحث
                    </button>
                    <a href="<?php echo BASE_URL; ?>/search.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-undo ms-1"></i> إعادة تعيين
                    </a>
                </form>
            </div>
        </div>

        <!-- Results -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
                <h4 class="fw-bold mb-0">
                    <i class="fas fa-hospital ms-1 text-primary"></i>
                    نتائج البحث
                    <span class="badge bg-primary ms-2"><?php echo count($results); ?> صيدلية</span>
                </h4>
                <div>
                    <span class="text-muted" style="font-size:0.85rem;">مرتبة حسب: الباقة والأحدث</span>
                </div>
            </div>

            <?php if (empty($results)): ?>
            <div class="text-center py-5" data-aos="fade-up">
                <i class="fas fa-search" style="font-size:4rem;color:var(--gray-300);"></i>
                <h5 class="mt-3 text-muted">لا توجد نتائج للبحث</h5>
                <p class="text-muted">حاول تغيير معايير البحث أو تصفح المحافظات</p>
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary"><i class="fas fa-home ms-1"></i> العودة للرئيسية</a>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($results as $pharmacy):
                    $tier_class = $pharmacy['tier_slug'] == 'large' ? 'premium' : ($pharmacy['tier_slug'] == 'medium' ? 'medium' : 'basic');
                ?>
                <div class="col-md-6" data-aos="fade-up">
                    <div class="pharmacy-card <?php echo $tier_class; ?>">
                        <?php if ($pharmacy['tier_slug'] == 'large'): ?>
                            <div class="card-badge"><i class="fas fa-crown ms-1"></i> <?php _e($pharmacy['tier_name']); ?></div>
                        <?php endif; ?>
                        <?php if ($pharmacy['tier_slug'] != 'basic'): ?>
                        <div class="card-image">
                            <?php if ($pharmacy['logo']): ?>
                                <img src="<?php echo UPLOADS_URL; ?>/photos/<?php echo $pharmacy['logo']; ?>" alt="<?php _e($pharmacy['name_ar']); ?>">
                            <?php else: ?>
                                <div class="placeholder-img"><i class="fas fa-hospital"></i></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h3 class="card-pharmacy-name"><?php _e($pharmacy['name_ar']); ?></h3>
                                <?php if ($pharmacy['tier_slug'] != 'large'): ?>
                                    <?php echo getTierBadge($pharmacy['tier_slug'], $pharmacy['tier_name']); ?>
                                <?php endif; ?>
                            </div>
                            <div class="card-location">
                                <i class="fas fa-map-marker-alt ms-1"></i>
                                <?php _e($pharmacy['city_name'] . ' - ' . $pharmacy['street_name']); ?>
                            </div>
                            <?php if ($pharmacy['tier_slug'] != 'basic'): ?>
                            <div class="card-features">
                                <?php if ($pharmacy['is_24hours']): ?>
                                    <span class="feature-tag green"><i class="fas fa-clock ms-1"></i> 24 ساعة</span>
                                <?php endif; ?>
                                <?php if ($pharmacy['has_delivery']): ?>
                                    <span class="feature-tag blue"><i class="fas fa-truck ms-1"></i> توصيل</span>
                                <?php endif; ?>
                                <span class="feature-tag"><?php _e($pharmacy['governorate_name']); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="card-contact mt-2">
                                <a href="tel:<?php echo $pharmacy['phone']; ?>" class="btn-contact btn-call">
                                    <i class="fas fa-phone-alt ms-1"></i> اتصال
                                </a>
                                <?php if ($pharmacy['whatsapp'] && $pharmacy['tier_slug'] == 'large'): ?>
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $pharmacy['whatsapp']); ?>" target="_blank" class="btn-contact btn-whatsapp">
                                        <i class="fab fa-whatsapp ms-1"></i> واتساب
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>/pharmacy.php?id=<?php echo $pharmacy['id']; ?>" class="btn-contact btn-map">
                                    <i class="fas fa-info-circle ms-1"></i> تفاصيل
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
