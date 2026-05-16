<?php
require_once __DIR__ . '/config/app.php';

$page_title = 'الباقات والأسعار - دليل الصيدليات المصرية';
$show_header = true;
$breadcrumbs = [
    ['label' => 'الرئيسية', 'url' => BASE_URL],
    ['label' => 'الباقات والأسعار'],
];

$tiers = getTiers();

include __DIR__ . '/includes/header.php';
?>

<section class="section-padding">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div class="section-divider"></div>
            <h2 class="section-title">اختر الباقة المناسبة لصيدليتك</h2>
            <p class="section-subtitle">باقات متنوعة تناسب جميع الاحتياجات. ابدأ مجاناً وطور حضورك</p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($tiers as $tier):
                $is_featured = $tier['slug'] == 'large';
                $icon = 'fa-hospital';
                if ($tier['slug'] == 'large') $icon = 'fa-crown';
                elseif ($tier['slug'] == 'medium') $icon = 'fa-star';
                else $icon = 'fa-id-card';
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="pricing-card <?php echo $is_featured ? 'featured' : ''; ?>">
                    <?php if ($is_featured): ?>
                        <div class="position-absolute top-0 start-50 translate-middle badge bg-warning text-dark px-3 py-2" style="font-size:0.78rem;">
                            <i class="fas fa-fire ms-1"></i> الأكثر طلباً
                        </div>
                    <?php endif; ?>
                    <div class="pricing-icon">
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    <h3 class="pricing-name"><?php _e($tier['name_ar']); ?></h3>
                    <div class="pricing-price">
                        <?php echo $tier['price'] > 0 ? number_format($tier['price']) : 'مجاني'; ?>
                        <?php if ($tier['price'] > 0): ?>
                            <span>/ سنوياً</span>
                        <?php endif; ?>
                    </div>

                    <ul class="pricing-features">
                        <li>
                            <i class="fas <?php echo $tier['has_logo'] ? 'fa-check' : 'fa-times'; ?>"></i>
                            شعار الصيدلية
                        </li>
                        <li>
                            <i class="fas <?php echo $tier['photos_limit'] > 0 ? 'fa-check' : 'fa-times'; ?>"></i>
                            <?php echo $tier['photos_limit'] > 0 ? "ما يصل إلى {$tier['photos_limit']} صور" : 'بدون صور'; ?>
                        </li>
                        <li>
                            <i class="fas <?php echo $tier['has_whatsapp'] ? 'fa-check' : 'fa-times'; ?>"></i>
                            زر واتساب للتواصل المباشر
                        </li>
                        <li>
                            <i class="fas <?php echo $tier['has_map'] ? 'fa-check' : 'fa-times'; ?>"></i>
                            خريطة الموقع
                        </li>
                        <li>
                            <i class="fas <?php echo $tier['has_license_upload'] ? 'fa-check' : 'fa-times'; ?>"></i>
                            رفع الترخيص للتحقق
                        </li>
                        <li>
                            <i class="fas <?php echo $tier['is_featured'] ? 'fa-check' : 'fa-times'; ?>"></i>
                            ظهور مميز في أول النتائج
                        </li>
                    </ul>

                    <a href="<?php echo BASE_URL; ?>/add-pharmacy.php?tier=<?php echo $tier['slug']; ?>" class="btn-choose">
                        <i class="fas fa-plus-circle ms-1"></i>
                        <?php echo $tier['price'] > 0 ? 'اشتراك الآن' : 'ابدأ مجاناً'; ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div class="section-divider"></div>
            <h2 class="section-title">الأسئلة الشائعة</h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="pricingFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                كيف يمكنني إضافة صيدليتي؟
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">يمكنك التسجيل في الموقع ثم الضغط على "إضافة صيدلية" وملء النموذج. بعد المراجعة من قبل الإدارة، سيتم نشر الصيدلية.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                كيف يمكنني ترقية بطاقة صيدليتي؟
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">يمكنك التواصل مع الإدارة عبر صفحة اتصل بنا لترقية الباقة. سيتم تفعيل الميزات الإضافية فور الدفع.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                ما هي صيدليات الحراسة؟
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">صيدليات الحراسة هي الصيدليات التي تعمل على مدار 24 ساعة طوال أيام الأسبوع، وتوفر خدمات الطوارئ على مدار الساعة.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                هل هناك فترة تجريبية مجانية؟
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">نعم، الباقة الأساسية مجانية تماماً وتتضمن المعلومات الأساسية ورقم الهاتف.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
