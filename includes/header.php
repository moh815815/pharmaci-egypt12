<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#0056b3">
    <meta name="robots" content="index, follow">
    <meta name="description" content="<?php _e($meta_description ?? 'دليل الصيدليات المصرية - أكبر دليل صيدليات في مصر. ابحث عن أقرب صيدلية، صيدليات 24 ساعة، وأرقام التواصل مع الصيدليات في جميع المحافظات.'); ?>">
    <meta name="keywords" content="صيدليات مصر, دليل الصيدليات, صيدلية 24 ساعة, صيدليات, مصر, محافظات مصر, صيدلية حراسة, روشتة, دواء">

    <!-- Open Graph -->
    <meta property="og:title" content="<?php _e($page_title ?? 'دليل الصيدليات المصرية'); ?>">
    <meta property="og:description" content="<?php _e($meta_description ?? 'أكبر دليل صيدليات في مصر - ابحث عن أقرب صيدلية لك'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo BASE_URL; ?>/">
    <meta property="og:site_name" content="دليل الصيدليات المصرية">
    <meta property="og:locale" content="ar_AR">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php _e($page_title ?? 'دليل الصيدليات المصرية'); ?>">
    <meta name="twitter:description" content="<?php _e($meta_description ?? 'أكبر دليل صيدليات في مصر'); ?>">

    <!-- Canonical -->
    <link rel="canonical" href="<?php echo BASE_URL . ($_SERVER['REQUEST_URI'] ?? '/'); ?>">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💊</text></svg>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">

    <!-- Structured Data (Organization) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "دليل الصيدليات المصرية",
        "url": "<?php echo BASE_URL; ?>",
        "logo": "<?php echo BASE_URL; ?>/assets/images/logo.png",
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+20-100-000-0000",
            "contactType": "customer service",
            "areaServed": "EG"
        },
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "EG"
        }
    }
    </script>

    <title><?php _e($page_title ?? 'دليل الصيدليات المصرية'); ?></title>
</head>
<body>

<!-- ====== Top Bar ====== -->
<div class="top-bar">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 col-12 top-bar-right">
                <span class="top-bar-text"><i class="fas fa-phone-alt ms-1"></i> 01000000000</span>
                <span class="top-bar-text mx-3 d-none d-sm-inline"><i class="fas fa-envelope ms-1"></i> info@pharmaci-egypt.com</span>
            </div>
            <div class="col-md-6 col-12 top-bar-left text-start">
                <?php if (isAdminLoggedIn()): ?>
                    <a href="<?php echo ADMIN_URL; ?>/dashboard.php" class="top-bar-link"><i class="fas fa-cog ms-1"></i> لوحة التحكم</a>
                <?php endif; ?>
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="top-bar-link"><i class="fas fa-sign-out-alt ms-1"></i> تسجيل خروج</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/auth/login.php" class="top-bar-link"><i class="fas fa-user ms-1"></i> دخول</a>
                    <a href="<?php echo BASE_URL; ?>/auth/register.php" class="top-bar-link me-2"><i class="fas fa-user-plus ms-1"></i> تسجيل</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ====== Main Header / Navbar ====== -->
<nav class="navbar navbar-expand-lg main-navbar sticky-top" role="navigation">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>" aria-label="دليل الصيدليات المصرية">
            <div class="brand-wrapper">
                <i class="fas fa-hospital-alt brand-icon"></i>
                <div class="brand-text">
                    <span class="brand-title">دليل الصيدليات المصرية</span>
                    <span class="brand-subtitle d-none d-sm-block">أكبر دليل صيدليات في مصر</span>
                </div>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="فتح القائمة">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'] ?? '') == 'index.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>">
                        <i class="fas fa-home ms-1"></i> الرئيسية
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'] ?? '') == 'search.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/search.php">
                        <i class="fas fa-search ms-1"></i> بحث متقدم
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'] ?? '') == 'pricing.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pricing.php">
                        <i class="fas fa-tags ms-1"></i> الباقات والأسعار
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'] ?? '') == 'add-pharmacy.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/add-pharmacy.php">
                        <i class="fas fa-plus-circle ms-1"></i> إضافة صيدلية
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'] ?? '') == 'contact.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/contact.php">
                        <i class="fas fa-envelope ms-1"></i> اتصل بنا
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ====== Page Header ====== -->
<?php if (isset($show_header) && $show_header): ?>
<div class="page-header">
    <div class="container">
        <h1 class="page-title"><?php _e($page_title ?? ''); ?></h1>
        <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
        <nav aria-label="مسار الصفحة">
            <ol class="breadcrumb">
                <?php foreach ($breadcrumbs as $crumb): ?>
                    <?php if (isset($crumb['url'])): ?>
                        <li class="breadcrumb-item"><a href="<?php echo $crumb['url']; ?>"><?php _e($crumb['label']); ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php _e($crumb['label']); ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- ====== Flash Messages ====== -->
<?php if (isset($_SESSION['flash'])): ?>
<div class="container mt-3">
    <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $_SESSION['flash']['type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?> ms-1"></i>
        <?php echo $_SESSION['flash']['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
    </div>
</div>
<?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<main id="main-content">
