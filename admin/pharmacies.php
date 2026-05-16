<?php
require_once __DIR__ . '/../config/app.php';
$page_title = 'إدارة الصيدليات';
include __DIR__ . '/includes/header.php';

$pdo = getDB();

// Handle actions
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Approve / Reject
if (isset($_GET['approve']) && $id) {
    $pdo->prepare("UPDATE pharmacies SET status = 'approved' WHERE id = ?")->execute([$id]);
    logActivity($_SESSION['admin_id'], 'قبول صيدلية', "تم قبول الصيدلية رقم $id");
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم قبول الصيدلية بنجاح'];
    redirect(ADMIN_URL . '/pharmacies.php');
}
if (isset($_GET['reject']) && $id) {
    $pdo->prepare("UPDATE pharmacies SET status = 'rejected' WHERE id = ?")->execute([$id]);
    logActivity($_SESSION['admin_id'], 'رفض صيدلية', "تم رفض الصيدلية رقم $id");
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم رفض الصيدلية'];
    redirect(ADMIN_URL . '/pharmacies.php');
}
if (isset($_GET['suspend']) && $id) {
    $pdo->prepare("UPDATE pharmacies SET status = 'suspended' WHERE id = ?")->execute([$id]);
    logActivity($_SESSION['admin_id'], 'إيقاف صيدلية', "تم إيقاف الصيدلية رقم $id");
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم إيقاف الصيدلية'];
    redirect(ADMIN_URL . '/pharmacies.php');
}

// Change tier
if (isset($_GET['change_tier']) && isset($_GET['tier_id']) && $id) {
    $tier_id = (int)$_GET['tier_id'];
    $pdo->prepare("UPDATE pharmacies SET tier_id = ? WHERE id = ?")->execute([$tier_id, $id]);
    logActivity($_SESSION['admin_id'], 'تغيير نوع الاشتراك', "تم تغيير اشتراك الصيدلية رقم $id إلى $tier_id");
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم تغيير نوع الاشتراك بنجاح'];
    redirect(ADMIN_URL . '/pharmacies.php');
}

// Delete
if (isset($_GET['delete']) && $id && $_SESSION['admin_role'] == 'super_admin') {
    $pdo->prepare("DELETE FROM pharmacies WHERE id = ?")->execute([$id]);
    logActivity($_SESSION['admin_id'], 'حذف صيدلية', "تم حذف الصيدلية رقم $id");
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم حذف الصيدلية'];
    redirect(ADMIN_URL . '/pharmacies.php');
}

// Update pharmacy (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action == 'edit' && $id) {
    $name_ar = sanitize($_POST['name_ar']);
    $phone = sanitize($_POST['phone']);
    $whatsapp = sanitize($_POST['whatsapp']);
    $tier_id = (int)$_POST['tier_id'];
    $status = sanitize($_POST['status']);
    $is_24hours = isset($_POST['is_24hours']) ? 1 : 0;
    $has_delivery = isset($_POST['has_delivery']) ? 1 : 0;
    $governorate_id = (int)$_POST['governorate_id'];
    $city_id = (int)$_POST['city_id'];
    $street_id = (int)$_POST['street_id'];
    $latitude = sanitize($_POST['latitude']);
    $longitude = sanitize($_POST['longitude']);

    $pdo->prepare("UPDATE pharmacies SET name_ar=?, phone=?, whatsapp=?, tier_id=?, status=?, is_24hours=?, has_delivery=?, governorate_id=?, city_id=?, street_id=?, latitude=?, longitude=? WHERE id=?")
        ->execute([$name_ar, $phone, $whatsapp, $tier_id, $status, $is_24hours, $has_delivery, $governorate_id, $city_id, $street_id, $latitude, $longitude, $id]);

    logActivity($_SESSION['admin_id'], 'تعديل صيدلية', "تم تعديل بيانات الصيدلية رقم $id");
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم تحديث بيانات الصيدلية بنجاح'];
    redirect(ADMIN_URL . '/pharmacies.php');
}

$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search_query = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Edit mode
if ($action == 'edit' && $id):
    $pharmacy = getPharmacy($id);
    if (!$pharmacy) { echo '<div class="alert alert-danger">الصيدلية غير موجودة</div>'; include __DIR__ . '/includes/footer.php'; exit; }
    $governorates = getGovernorates();
    $cities = getCitiesByGovernorate($pharmacy['governorate_id']);
    $streets = getStreetsByCity($pharmacy['city_id']);
    $tiers = getTiers();
?>

<div class="admin-header">
    <h4><i class="fas fa-edit ms-1"></i> تعديل الصيدلية: <?php _e($pharmacy['name_ar']); ?></h4>
    <a href="<?php echo ADMIN_URL; ?>/pharmacies.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right ms-1"></i> العودة</a>
</div>

<div class="form-section">
    <form method="POST">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">اسم الصيدلية</label>
                <input type="text" name="name_ar" class="form-control" value="<?php _e($pharmacy['name_ar']); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">الهاتف</label>
                <input type="text" name="phone" class="form-control" value="<?php _e($pharmacy['phone']); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">واتساب</label>
                <input type="text" name="whatsapp" class="form-control" value="<?php _e($pharmacy['whatsapp']); ?>">
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label class="form-label">المحافظة</label>
                <select name="governorate_id" class="form-select">
                    <?php foreach ($governorates as $gov): ?>
                        <option value="<?php echo $gov['id']; ?>" <?php echo $pharmacy['governorate_id'] == $gov['id'] ? 'selected' : ''; ?>><?php _e($gov['name_ar']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">المدينة</label>
                <select name="city_id" class="form-select">
                    <?php foreach ($cities as $city): ?>
                        <option value="<?php echo $city['id']; ?>" <?php echo $pharmacy['city_id'] == $city['id'] ? 'selected' : ''; ?>><?php _e($city['name_ar']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">الشارع</label>
                <select name="street_id" class="form-select">
                    <?php foreach ($streets as $street): ?>
                        <option value="<?php echo $street['id']; ?>" <?php echo $pharmacy['street_id'] == $street['id'] ? 'selected' : ''; ?>><?php _e($street['name_ar']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-3">
                <label class="form-label">نوع الاشتراك</label>
                <select name="tier_id" class="form-select">
                    <?php foreach ($tiers as $tier): ?>
                        <option value="<?php echo $tier['id']; ?>" <?php echo $pharmacy['tier_id'] == $tier['id'] ? 'selected' : ''; ?>><?php _e($tier['name_ar']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">الحالة</label>
                <select name="status" class="form-select">
                    <option value="approved" <?php echo $pharmacy['status'] == 'approved' ? 'selected' : ''; ?>>مقبول</option>
                    <option value="pending" <?php echo $pharmacy['status'] == 'pending' ? 'selected' : ''; ?>>قيد المراجعة</option>
                    <option value="rejected" <?php echo $pharmacy['status'] == 'rejected' ? 'selected' : ''; ?>>مرفوض</option>
                    <option value="suspended" <?php echo $pharmacy['status'] == 'suspended' ? 'selected' : ''; ?>>موقوف</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">خط العرض</label>
                <input type="text" name="latitude" class="form-control" value="<?php _e($pharmacy['latitude']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">خط الطول</label>
                <input type="text" name="longitude" class="form-control" value="<?php _e($pharmacy['longitude']); ?>">
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_24hours" value="1" id="e24h" <?php echo $pharmacy['is_24hours'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="e24h">صيدلية 24 ساعة</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="has_delivery" value="1" id="edel" <?php echo $pharmacy['has_delivery'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="edel">خدمة التوصيل</label>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save ms-1"></i> حفظ التغييرات</button>
            <a href="<?php echo ADMIN_URL; ?>/pharmacies.php" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>

<?php else: // List view ?>

<div class="admin-header">
    <h4><i class="fas fa-hospital ms-1"></i> إدارة الصيدليات</h4>
    <div>
        <span class="text-muted" style="font-size:0.85rem;">إجمالي: <?php echo $total_pharmacies; ?></span>
    </div>
</div>

<!-- Filters -->
<div class="form-section mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="بحث باسم الصيدلية..." value="<?php _e($search_query); ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">جميع الحالات</option>
                <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>مقبول</option>
                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>قيد المراجعة</option>
                <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>مرفوض</option>
                <option value="suspended" <?php echo $status_filter == 'suspended' ? 'selected' : ''; ?>>موقوف</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search ms-1"></i> بحث</button>
        </div>
        <div class="col-md-2">
            <a href="<?php echo ADMIN_URL; ?>/pharmacies.php" class="btn btn-outline-secondary w-100"><i class="fas fa-undo ms-1"></i> إعادة</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="admin-table">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الصيدلية</th>
                    <th>الهاتف</th>
                    <th>المحافظة</th>
                    <th>نوع الاشتراك</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $where = "WHERE 1=1";
                $binds = [];
                if ($status_filter) {
                    $where .= " AND p.status = ?";
                    $binds[] = $status_filter;
                }
                if ($search_query) {
                    $where .= " AND p.name_ar LIKE ?";
                    $binds[] = '%' . $search_query . '%';
                }

                $stmt = $pdo->prepare("
                    SELECT p.*, t.name_ar as tier_name, t.slug as tier_slug,
                           g.name_ar as governorate_name
                    FROM pharmacies p
                    JOIN pharmacy_tiers t ON p.tier_id = t.id
                    JOIN governorates g ON p.governorate_id = g.id
                    $where
                    ORDER BY p.created_at DESC
                ");
                $stmt->execute($binds);
                $pharmacies = $stmt->fetchAll();

                if ($pharmacies):
                    $i = 1;
                    foreach ($pharmacies as $p):
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td>
                        <strong><?php _e($p['name_ar']); ?></strong>
                        <br><small class="text-muted"><?php _e($p['city_id'] ? '' : ''); ?></small>
                    </td>
                    <td dir="ltr"><?php _e($p['phone']); ?></td>
                    <td><?php _e($p['governorate_name']); ?></td>
                    <td><?php echo getTierBadge($p['tier_slug'], $p['tier_name']); ?></td>
                    <td><?php echo getStatusBadge($p['status']); ?></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="<?php echo ADMIN_URL; ?>/pharmacies.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-outline-primary" title="تعديل"><i class="fas fa-edit"></i></a>
                            <?php if ($p['status'] != 'approved'): ?>
                                <a href="<?php echo ADMIN_URL; ?>/pharmacies.php?approve=1&id=<?php echo $p['id']; ?>" class="btn btn-outline-success" title="قبول" data-confirm="هل أنت متأكد من قبول هذه الصيدلية؟"><i class="fas fa-check"></i></a>
                            <?php endif; ?>
                            <?php if ($p['status'] != 'rejected'): ?>
                                <a href="<?php echo ADMIN_URL; ?>/pharmacies.php?reject=1&id=<?php echo $p['id']; ?>" class="btn btn-outline-danger" title="رفض" data-confirm="هل أنت متأكد من رفض هذه الصيدلية؟"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                            <?php if ($_SESSION['admin_role'] == 'super_admin'): ?>
                                <a href="<?php echo ADMIN_URL; ?>/pharmacies.php?delete=1&id=<?php echo $p['id']; ?>" class="btn btn-outline-danger" title="حذف" data-confirm="هل أنت متأكد من حذف هذه الصيدلية؟ لا يمكن التراجع عن هذا الإجراء."><i class="fas fa-trash"></i></a>
                            <?php endif; ?>
                        </div>
                        <!-- Quick tier change -->
                        <div class="mt-1">
                            <form method="GET" action="<?php echo ADMIN_URL; ?>/pharmacies.php" class="d-flex gap-1" style="font-size:0.75rem;">
                                <input type="hidden" name="change_tier" value="1">
                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                <select name="tier_id" class="form-select form-select-sm" style="width:auto;font-size:0.7rem;">
                                    <?php $all_tiers = getTiers(); foreach ($all_tiers as $t): ?>
                                        <option value="<?php echo $t['id']; ?>" <?php echo $p['tier_id'] == $t['id'] ? 'selected' : ''; ?>><?php _e($t['name_ar']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-info" style="font-size:0.7rem;">تغيير</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7" class="text-center text-muted py-4">لا توجد صيدليات</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
