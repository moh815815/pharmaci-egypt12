</main>

<!-- ====== Footer ====== -->
<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand">
                    <i class="fas fa-hospital-alt fa-2x mb-3" aria-hidden="true"></i>
                    <h5>دليل الصيدليات المصرية</h5>
                    <p>أكبر وأشمل دليل للصيدليات في جميع محافظات مصر. نهدف إلى تسهيل الوصول إلى الصيدليات القريبة منك على مدار الساعة.</p>
                </div>
                <div class="footer-social">
                    <a href="#" class="social-link" title="فيسبوك" aria-label="فيسبوك"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link" title="تويتر" aria-label="تويتر"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link" title="واتساب" aria-label="واتساب"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" class="social-link" title="انستجرام" aria-label="انستجرام"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6 class="footer-heading">روابط سريعة</h6>
                <ul class="footer-links">
                    <li><a href="<?php echo BASE_URL; ?>"><i class="fas fa-chevron-left ms-1"></i> الرئيسية</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/search.php"><i class="fas fa-chevron-left ms-1"></i> بحث عن صيدلية</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/pricing.php"><i class="fas fa-chevron-left ms-1"></i> الباقات والأسعار</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/add-pharmacy.php"><i class="fas fa-chevron-left ms-1"></i> إضافة صيدلية</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/contact.php"><i class="fas fa-chevron-left ms-1"></i> اتصل بنا</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-heading">المحافظات الرئيسية</h6>
                <ul class="footer-links" id="footerGovernorates">
                    <li class="text-muted" style="font-size:0.82rem;"><i class="fas fa-spinner fa-spin ms-1"></i> جاري التحميل...</li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-heading">معلومات الاتصال</h6>
                <ul class="footer-contact">
                    <li><i class="fas fa-map-marker-alt ms-2"></i> القاهرة - مصر</li>
                    <li><i class="fas fa-phone-alt ms-2"></i> 01000000000</li>
                    <li><i class="fas fa-envelope ms-2"></i> info@pharmaci-egypt.com</li>
                    <li><i class="fas fa-clock ms-2"></i> الدعم: 24 ساعة</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> دليل الصيدليات المصرية. جميع الحقوق محفوظة.</p>
                </div>
                <div class="col-md-6 text-start">
                    <p class="mb-0">تصميم وبرمجة: <a href="#" class="text-white">فريق دليل الصيدليات</a></p>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Scroll to Top -->
<button id="scrollTopBtn" class="scroll-top-btn" title="العودة للأعلى" aria-label="العودة للأعلى">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Scripts -->
<script>var BASE_URL = '<?php echo BASE_URL; ?>';</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="<?php echo ASSETS_URL; ?>/js/main.js" defer></script>
<script>
    AOS.init({ duration: 600, once: true, disable: window.innerWidth < 768 });
</script>
</body>
</html>
