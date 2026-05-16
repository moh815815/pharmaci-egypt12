<?php
require_once __DIR__ . '/../config/app.php';
$page_title = 'لوحة الإحصائيات';
include __DIR__ . '/includes/header.php';

$stats = getDashboardStats();
?>

<div class="admin-header">
    <h4><i class="fas fa-chart-pie ms-1"></i> لوحة الإحصائيات</h4>
    <span class="text-muted">آخر تحديث: <?php echo date('Y/m/d h:i A'); ?></span>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon bg-medical-light text-medical"><i class="fas fa-hospital"></i></div>
            <div class="stat-number"><?php echo number_format($stats['total_pharmacies']); ?></div>
            <div class="stat-label">إجمالي الصيدليات</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff3cd;color:#856404;"><i class="fas fa-clock"></i></div>
            <div class="stat-number"><?php echo number_format($stats['pending_pharmacies']); ?></div>
            <div class="stat-label">قيد المراجعة</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d4edda;color:#155724;"><i class="fas fa-check-circle"></i></div>
            <div class="stat-number"><?php echo number_format($stats['approved_pharmacies']); ?></div>
            <div class="stat-label">مقبولة</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#cce5ff;color:#004085;"><i class="fas fa-users"></i></div>
            <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
            <div class="stat-label">المستخدمين</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8d5f5;color:#6f42c1;"><i class="fas fa-map"></i></div>
            <div class="stat-number"><?php echo $stats['total_governorates']; ?></div>
            <div class="stat-label">محافظة</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1ecf1;color:#0c5460;"><i class="fas fa-city"></i></div>
            <div class="stat-number"><?php echo $stats['total_cities']; ?></div>
            <div class="stat-label">مدينة / منطقة</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f8d7da;color:#721c24;"><i class="fas fa-road"></i></div>
            <div class="stat-number"><?php echo $stats['total_streets']; ?></div>
            <div class="stat-label">شارع رئيسي</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d4edda;color:#155724;"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-number"><?php echo $stats['today_pharmacies']; ?></div>
            <div class="stat-label">جديد اليوم</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Pharmacies by Tier -->
    <div class="col-md-6">
        <div class="admin-table p-3">
            <h5 class="fw-bold mb-3"><i class="fas fa-tags ms-1"></i> الصيدليات حسب نوع الاشتراك</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>نوع البطاقة</th>
                        <th>العدد</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['by_tier'] as $tier): ?>
                    <tr>
                        <td><?php _e($tier['name_ar']); ?></td>
                        <td><span class="badge bg-primary"><?php echo $tier['total']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pending Pharmacies -->
    <div class="col-md-6">
        <div class="admin-table p-3">
            <h5 class="fw-bold mb-3"><i class="fas fa-clock ms-1"></i> أحدث الصيدليات قيد المراجعة</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>الهاتف</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $pending_list = $pdo->query("
                        SELECT p.id, p.name_ar, p.phone, p.created_at, t.name_ar as tier_name
                        FROM pharmacies p
                        JOIN pharmacy_tiers t ON p.tier_id = t.id
                        WHERE p.status = 'pending'
                        ORDER BY p.created_at DESC
                        LIMIT 10
                    ")->fetchAll();
                    if ($pending_list):
                        foreach ($pending_list as $p):
                    ?>
                    <tr>
                        <td><a href="<?php echo ADMIN_URL; ?>/pharmacies.php?action=edit&id=<?php echo $p['id']; ?>"><?php _e($p['name_ar']); ?></a></td>
                        <td dir="ltr"><?php _e($p['phone']); ?></td>
                        <td><?php echo formatDate($p['created_at'], 'Y/m/d'); ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="3" class="text-center text-muted">لا توجد صيدليات في انتظار المراجعة</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($stats['pending_pharmacies'] > 0): ?>
            <a href="<?php echo ADMIN_URL; ?>/pharmacies.php?status=pending" class="btn btn-sm btn-warning">عرض الكل</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
