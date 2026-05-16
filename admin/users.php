<?php
require_once __DIR__ . '/../config/app.php';
$page_title = 'إدارة المستخدمين';
include __DIR__ . '/includes/header.php';

$pdo = getDB();

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $user = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
    $user->execute([$id]);
    $u = $user->fetch();
    if ($u) {
        $new = $u['is_active'] ? 0 : 1;
        $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$new, $id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم تغيير حالة المستخدم'];
    }
    redirect(ADMIN_URL . '/users.php');
}

$users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM pharmacies WHERE user_id = u.id) as pharmacy_count FROM users u ORDER BY u.created_at DESC")->fetchAll();
?>

<div class="admin-header">
    <h4><i class="fas fa-users ms-1"></i> إدارة المستخدمين</h4>
    <span class="text-muted">إجمالي: <?php echo count($users); ?></span>
</div>

<div class="admin-table">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>رقم الهاتف</th>
                    <th>البريد</th>
                    <th>عدد الصيدليات</th>
                    <th>الحالة</th>
                    <th>تاريخ التسجيل</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($users as $u): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php _e($u['name']); ?></td>
                    <td dir="ltr"><?php _e($u['phone']); ?></td>
                    <td><?php _e($u['email'] ?: '-'); ?></td>
                    <td><span class="badge bg-info"><?php echo $u['pharmacy_count']; ?></span></td>
                    <td><?php echo $u['is_active'] ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-secondary">موقوف</span>'; ?></td>
                    <td><?php echo formatDate($u['created_at'], 'Y/m/d'); ?></td>
                    <td>
                        <a href="?toggle=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-<?php echo $u['is_active'] ? 'warning' : 'success'; ?>" data-confirm="تغيير حالة المستخدم؟">
                            <i class="fas fa-<?php echo $u['is_active'] ? 'ban' : 'check'; ?>"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
