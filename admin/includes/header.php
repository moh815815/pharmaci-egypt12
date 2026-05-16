<?php
requireAdminLogin();
ensureLogsTable();

$current_admin = getCurrentAdmin();
if (!$current_admin) {
    session_destroy();
    redirect(ADMIN_URL . '/login.php');
}

// Get counts for sidebar
$pdo = getDB();
$pending_count = $pdo ? $pdo->query("SELECT COUNT(*) FROM pharmacies WHERE status = 'pending'")->fetchColumn() : 0;
$total_pharmacies = $pdo ? $pdo->query("SELECT COUNT(*) FROM pharmacies")->fetchColumn() : 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#212529">
    <title>لوحة التحكم - <?php _e($page_title ?? 'دليل الصيدليات'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
</head>
<body>
<div class="admin-wrapper">
    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-hospital-alt fa-2x mb-2"></i>
            <h5>دليل الصيدليات</h5>
            <small>لوحة التحكم</small>
        </div>
        <hr style="border-color:rgba(255,255,255,0.1);">
        <div class="sidebar-user text-center mb-3">
            <i class="fas fa-user-circle fa-2x mb-1" style="color:rgba(255,255,255,0.6);"></i>
            <div class="text-white" style="font-size:0.85rem;"><?php _e($current_admin['name']); ?></div>
            <span class="badge bg-<?php echo $current_admin['role'] == 'super_admin' ? 'danger' : 'info'; ?>" style="font-size:0.7rem;">
                <?php echo $current_admin['role'] == 'super_admin' ? 'مدير عام' : ($current_admin['role'] == 'admin' ? 'مشرف' : 'مساعد'); ?>
            </span>
        </div>
        <hr style="border-color:rgba(255,255,255,0.1);">

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/dashboard.php">
                    <i class="fas fa-chart-pie"></i> لوحة الإحصائيات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pharmacies.php' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/pharmacies.php">
                    <i class="fas fa-hospital"></i> إدارة الصيدليات
                    <?php if ($pending_count > 0): ?>
                        <span class="badge bg-danger float-start"><?php echo $pending_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'governorates.php' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/governorates.php">
                    <i class="fas fa-map"></i> المحافظات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cities.php' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/cities.php">
                    <i class="fas fa-city"></i> المدن / المناطق
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'streets.php' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/streets.php">
                    <i class="fas fa-road"></i> الشوارع الرئيسية
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tiers.php' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/tiers.php">
                    <i class="fas fa-tags"></i> أنواع الاشتراكات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/users.php">
                    <i class="fas fa-users"></i> المستخدمين
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/messages.php">
                    <i class="fas fa-envelope"></i> الرسائل
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/settings.php">
                    <i class="fas fa-cog"></i> الإعدادات
                </a>
            </li>
        </ul>
        <hr style="border-color:rgba(255,255,255,0.1);">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>" target="_blank">
                    <i class="fas fa-external-link-alt"></i> زيارة الموقع
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo ADMIN_URL; ?>/logout.php">
                    <i class="fas fa-sign-out-alt"></i> تسجيل خروج
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="admin-main">
        <button class="btn btn-dark mb-3 d-lg-none" id="sidebarToggle">
            <i class="fas fa-bars"></i> القائمة
        </button>
