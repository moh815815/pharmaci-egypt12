<?php
require_once __DIR__ . '/config/app.php';

$page_title = 'اتصل بنا - دليل الصيدليات المصرية';
$show_header = true;
$breadcrumbs = [
    ['label' => 'الرئيسية', 'url' => BASE_URL],
    ['label' => 'اتصل بنا'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name) || empty($message)) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'يرجى ملء الحقول المطلوبة'];
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $subject, $message]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم إرسال رسالتك بنجاح. سنتواصل معك قريباً.'];
        redirect(BASE_URL . '/contact.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-6" data-aos="fade-left">
            <div class="form-section">
                <h4 class="fw-bold mb-4"><i class="fas fa-envelope text-primary ms-1"></i> أرسل لنا رسالة</h4>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" dir="ltr">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control" dir="ltr">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الموضوع</label>
                        <select name="subject" class="form-select">
                            <option value="استفسار عام">استفسار عام</option>
                            <option value="طلب ترقية الباقة">طلب ترقية الباقة</option>
                            <option value="مشكلة في الموقع">مشكلة في الموقع</option>
                            <option value="اقتراح">اقتراح</option>
                            <option value="شكوى">شكوى</option>
                            <option value="أخرى">أخرى</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرسالة <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn-submit"><i class="fas fa-paper-plane ms-1"></i> إرسال الرسالة</button>
                </form>
            </div>
        </div>
        <div class="col-lg-6" data-aos="fade-right">
            <div class="form-section h-100">
                <h4 class="fw-bold mb-4"><i class="fas fa-info-circle text-primary ms-1"></i> معلومات الاتصال</h4>
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-map-marker-alt fa-fw text-primary ms-2" style="font-size:1.2rem;"></i>
                        <div>
                            <strong>العنوان</strong><br>
                            <span class="text-muted">القاهرة - مصر</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-phone-alt fa-fw text-primary ms-2" style="font-size:1.2rem;"></i>
                        <div>
                            <strong>الهاتف</strong><br>
                            <span class="text-muted" dir="ltr">01000000000</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fab fa-whatsapp fa-fw text-success ms-2" style="font-size:1.2rem;"></i>
                        <div>
                            <strong>واتساب</strong><br>
                            <span class="text-muted" dir="ltr">01000000000</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-envelope fa-fw text-primary ms-2" style="font-size:1.2rem;"></i>
                        <div>
                            <strong>البريد الإلكتروني</strong><br>
                            <span class="text-muted">info@pharmaci-egypt.com</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock fa-fw text-primary ms-2" style="font-size:1.2rem;"></i>
                        <div>
                            <strong>ساعات العمل</strong><br>
                            <span class="text-muted">طوال أيام الأسبوع - 24 ساعة</span>
                        </div>
                    </div>
                </div>
                <hr>
                <h5 class="fw-bold mb-3">تابعنا على</h5>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-primary"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="btn btn-outline-info"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="btn btn-outline-success"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" class="btn btn-outline-danger"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
