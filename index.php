<?php
require_once __DIR__ . '/config/app.php';

$page_title = 'دليل الصيدليات المصرية - أكبر دليل صيدليات في مصر';
$meta_description = 'أكبر دليل صيدليات في مصر. ابحث عن أقرب صيدلية لك في جميع المحافظات، صيدليات 24 ساعة، أرقام التواصل، والعناوين مع إمكانية الاتصال المباشر والملاحة.';

$pdo = getDB();
$has_db = $pdo !== null;

if ($has_db) {
    $total_pharmacies = $pdo->query("SELECT COUNT(*) FROM pharmacies WHERE status = 'approved'")->fetchColumn();
    $total_govs = $pdo->query("SELECT COUNT(*) FROM governorates WHERE status = 1")->fetchColumn();
    $total_cities = $pdo->query("SELECT COUNT(*) FROM cities WHERE status = 1")->fetchColumn();
    $featured = $pdo->query("
        SELECT p.*, t.name_ar as tier_name, t.slug as tier_slug,
               g.name_ar as governorate_name, c.name_ar as city_name, s.name_ar as street_name
        FROM pharmacies p
        JOIN pharmacy_tiers t ON p.tier_id = t.id
        JOIN governorates g ON p.governorate_id = g.id
        JOIN cities c ON p.city_id = c.id
        JOIN streets s ON p.street_id = s.id
        WHERE p.status = 'approved' AND t.slug = 'large'
        ORDER BY p.created_at DESC LIMIT 6
    ")->fetchAll();
    $latest = $pdo->query("
        SELECT p.*, t.name_ar as tier_name, t.slug as tier_slug,
               g.name_ar as governorate_name, c.name_ar as city_name, s.name_ar as street_name
        FROM pharmacies p
        JOIN pharmacy_tiers t ON p.tier_id = t.id
        JOIN governorates g ON p.governorate_id = g.id
        JOIN cities c ON p.city_id = c.id
        JOIN streets s ON p.street_id = s.id
        WHERE p.status = 'approved'
        ORDER BY p.created_at DESC LIMIT 12
    ")->fetchAll();
    $governorates = getGovernorates();
} else {
    $total_pharmacies = 0; $total_govs = 27; $total_cities = 0;
    $featured = []; $latest = []; $governorates = [];
}

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center hero-content">
            <div class="col-lg-7" data-aos="fade-left">
                <h1 class="hero-title">أكبر دليل صيدليات في مصر</h1>
                <p class="hero-subtitle">ابحث عن أقرب صيدلية لك في جميع محافظات مصر. صيدليات 24 ساعة، أرقام التواصل، والعناوين مع إمكانية الاتصال المباشر والملاحة.</p>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="hero-stat-number"><?php echo number_format($total_pharmacies); ?></span>
                        <span class="hero-stat-label">صيدلية مسجلة</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number"><?php echo $total_govs; ?></span>
                        <span class="hero-stat-label">محافظة</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number"><?php echo number_format($total_cities); ?></span>
                        <span class="hero-stat-label">مدينة ومنطقة</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block" data-aos="fade-right">
                <i class="fas fa-hospital-alt hero-bg-icon"></i>
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<div class="container">
    <div class="search-box" data-aos="fade-up">
        <form action="<?php echo BASE_URL; ?>/search.php" method="GET">
            <div class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <div class="search-input-wrap">
                        <i class="fas fa-search search-input-icon"></i>
                        <input type="text" id="quickSearch" name="search" class="form-control" placeholder="ابحث باسم الصيدلية أو الشارع..." autocomplete="off">
                        <div id="quickResults" class="quick-results"></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <select name="governorate_id" class="form-select">
                        <option value="">-- جميع المحافظات --</option>
                        <?php foreach ($governorates as $gov): ?>
                            <option value="<?php echo $gov['id']; ?>"><?php _e($gov['name_ar']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <select name="city_id" class="form-select" disabled>
                        <option value="">-- اختر المدينة --</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <button type="submit" class="btn-search w-100">
                        <i class="fas fa-search ms-1"></i> بحث
                    </button>
                </div>
            </div>
            <div class="search-quick-filters">
                <label class="filter-chip" data-input="filter_24h">
                    <i class="fas fa-clock"></i> صيدليات 24 ساعة
                </label>
                <label class="filter-chip" data-input="filter_delivery">
                    <i class="fas fa-truck"></i> خدمة التوصيل
                </label>
                <input type="hidden" name="is_24hours" id="filter_24h" value="">
                <input type="hidden" name="has_delivery" id="filter_delivery" value="">
            </div>
        </form>
    </div>
</div>

<?php if ($has_db && !empty($featured)): ?>
<section class="section-padding">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div class="section-divider"></div>
            <h2 class="section-title">صيدليات مميزة <i class="fas fa-crown text-warning"></i></h2>
            <p class="section-subtitle">صيدليات حاصلة على البطاقة الذهبية - الأكثر تميزاً في الخدمة</p>
        </div>
        <div class="row g-4">
            <?php foreach ($featured as $pharmacy): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="pharmacy-card premium">
                    <div class="card-badge"><i class="fas fa-crown ms-1"></i> <?php _e($pharmacy['tier_name']); ?></div>
                    <div class="card-image">
                        <?php if ($pharmacy['logo']): ?>
                            <img src="<?php echo UPLOADS_URL; ?>/photos/<?php echo $pharmacy['logo']; ?>" alt="<?php _e($pharmacy['name_ar']); ?>" loading="lazy">
                        <?php else: ?>
                            <div class="placeholder-img"><i class="fas fa-hospital"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3 class="card-pharmacy-name"><?php _e($pharmacy['name_ar']); ?></h3>
                        <p class="card-location"><i class="fas fa-map-marker-alt ms-1"></i> <?php _e($pharmacy['city_name'] . ' - ' . $pharmacy['street_name']); ?></p>
                        <div class="card-features">
                            <?php if ($pharmacy['is_24hours']): ?><span class="feature-tag green"><i class="fas fa-clock ms-1"></i> 24 ساعة</span><?php endif; ?>
                            <?php if ($pharmacy['has_delivery']): ?><span class="feature-tag blue"><i class="fas fa-truck ms-1"></i> توصيل</span><?php endif; ?>
                            <span class="feature-tag"><?php _e($pharmacy['governorate_name']); ?></span>
                        </div>
                        <div class="card-contact">
                            <a href="tel:<?php echo $pharmacy['phone']; ?>" class="btn-contact btn-call" rel="nofollow"><i class="fas fa-phone-alt ms-1"></i> اتصال</a>
                            <?php if ($pharmacy['whatsapp']): ?>
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $pharmacy['whatsapp']); ?>" target="_blank" class="btn-contact btn-whatsapp" rel="nofollow"><i class="fab fa-whatsapp ms-1"></i> واتساب</a>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>/pharmacy.php?id=<?php echo $pharmacy['id']; ?>" class="btn-contact btn-map"><i class="fas fa-info-circle"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($has_db && !empty($latest)): ?>
<section class="section-padding bg-white">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div class="section-divider"></div>
            <h2 class="section-title">أحدث الصيدليات المضافة</h2>
            <p class="section-subtitle">أحدث الصيدليات المنضمة إلى دليلنا</p>
        </div>
        <div class="row g-3">
            <?php foreach ($latest as $pharmacy):
                $tier_class = $pharmacy['tier_slug'] == 'large' ? 'premium' : ($pharmacy['tier_slug'] == 'medium' ? 'medium' : 'basic');
            ?>
            <div class="col-lg-3 col-md-4 col-6" data-aos="fade-up">
                <div class="pharmacy-card <?php echo $tier_class; ?>">
                    <?php if ($pharmacy['tier_slug'] == 'large'): ?>
                        <div class="card-badge"><i class="fas fa-crown ms-1"></i> <?php _e($pharmacy['tier_name']); ?></div>
                    <?php endif; ?>
                    <div class="card-image">
                        <?php if ($pharmacy['tier_slug'] != 'basic'): ?>
                            <?php if ($pharmacy['logo']): ?>
                                <img src="<?php echo UPLOADS_URL; ?>/photos/<?php echo $pharmacy['logo']; ?>" alt="<?php _e($pharmacy['name_ar']); ?>" loading="lazy">
                            <?php else: ?>
                                <div class="placeholder-img"><i class="fas fa-hospital"></i></div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3 class="card-pharmacy-name"><?php _e($pharmacy['name_ar']); ?></h3>
                        <p class="card-location"><i class="fas fa-map-marker-alt ms-1"></i> <?php _e($pharmacy['city_name']); ?></p>
                        <?php if ($pharmacy['tier_slug'] != 'basic'): ?>
                        <div class="card-features">
                            <?php if ($pharmacy['is_24hours']): ?><span class="feature-tag green">24 ساعة</span><?php endif; ?>
                            <?php if ($pharmacy['has_delivery']): ?><span class="feature-tag blue">توصيل</span><?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <div class="card-contact">
                            <a href="tel:<?php echo $pharmacy['phone']; ?>" class="btn-contact btn-call" rel="nofollow"><i class="fas fa-phone-alt"></i></a>
                            <?php if ($pharmacy['whatsapp'] && $pharmacy['tier_slug'] == 'large'): ?>
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $pharmacy['whatsapp']); ?>" target="_blank" class="btn-contact btn-whatsapp" rel="nofollow"><i class="fab fa-whatsapp"></i></a>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>/pharmacy.php?id=<?php echo $pharmacy['id']; ?>" class="btn-contact btn-map"><i class="fas fa-info-circle"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>/search.php" class="btn btn-primary btn-lg px-5"><i class="fas fa-search ms-1"></i> عرض جميع الصيدليات</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($has_db && !empty($governorates)): ?>
<section class="section-padding">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div class="section-divider"></div>
            <h2 class="section-title">جميع المحافظات</h2>
            <p class="section-subtitle">اختر محافظتك لتصفح الصيدليات المتاحة</p>
        </div>
        <div class="row g-3">
            <?php foreach ($governorates as $gov): ?>
            <div class="col-lg-2 col-md-3 col-4" data-aos="fade-up">
                <a href="<?php echo BASE_URL; ?>/search.php?governorate_id=<?php echo $gov['id']; ?>" class="governorate-card text-decoration-none">
                    <div class="gov-card text-center p-3 bg-white rounded-3 shadow-sm h-100">
                        <i class="fas fa-map-marker-alt text-primary mb-2" style="font-size:1.5rem;"></i>
                        <h6 class="mb-0 small"><?php _e($gov['name_ar']); ?></h6>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="section-padding cta-section">
    <div class="container text-center" data-aos="fade-up">
        <h2 class="text-white mb-3 fw-bold">هل تمتلك صيدلية؟</h2>
        <p class="text-white-50 mb-4 mx-auto cta-text">
            أضف صيدليتك الآن وانضم إلى أكبر دليل صيدليات في مصر. اختر الباقة المناسبة لك وابدأ في استقبال الزوار.
        </p>
        <a href="<?php echo BASE_URL; ?>/add-pharmacy.php" class="btn btn-light btn-lg px-5 fw-bold">
            <i class="fas fa-plus-circle ms-1"></i> أضف صيدليتك الآن
        </a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
