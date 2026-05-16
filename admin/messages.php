<?php
require_once __DIR__ . '/../config/app.php';
$page_title = 'الرسائل';
include __DIR__ . '/includes/header.php';

$pdo = getDB();

if (isset($_GET['read'])) {
    $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([(int)$_GET['read']]);
    redirect(ADMIN_URL . '/messages.php');
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([(int)$_GET['delete']]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم حذف الرسالة'];
    redirect(ADMIN_URL . '/messages.php');
}

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC")->fetchAll();
?>

<div class="admin-header">
    <h4><i class="fas fa-envelope ms-1"></i> الرسائل الواردة</h4>
</div>

<div class="admin-table">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>البريد / الهاتف</th>
                <th>الموضوع</th>
                <th>الرسالة</th>
                <th>الحالة</th>
                <th>التاريخ</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($messages as $m): ?>
            <tr class="<?php echo !$m['is_read'] ? 'fw-bold' : ''; ?>">
                <td><?php echo $i++; ?></td>
                <td><?php _e($m['name']); ?></td>
                <td style="font-size:0.82rem;">
                    <?php if ($m['email']): ?><div><?php _e($m['email']); ?></div><?php endif; ?>
                    <?php if ($m['phone']): ?><div dir="ltr"><?php _e($m['phone']); ?></div><?php endif; ?>
                </td>
                <td><?php _e($m['subject'] ?: '-'); ?></td>
                <td style="max-width:250px;">
                    <div class="text-truncate" style="max-width:200px;"><?php _e($m['message']); ?></div>
                </td>
                <td><?php echo $m['is_read'] ? '<span class="badge bg-secondary">مقروءة</span>' : '<span class="badge bg-warning text-dark">جديدة</span>'; ?></td>
                <td style="font-size:0.82rem;"><?php echo formatDate($m['created_at'], 'Y/m/d'); ?></td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <?php if (!$m['is_read']): ?>
                            <a href="?read=<?php echo $m['id']; ?>" class="btn btn-outline-success" title="تحديد كمقروءة"><i class="fas fa-check"></i></a>
                        <?php endif; ?>
                        <button class="btn btn-outline-primary" onclick="showMessage(<?php echo htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8'); ?>)"><i class="fas fa-eye"></i></button>
                        <a href="?delete=<?php echo $m['id']; ?>" class="btn btn-outline-danger" data-confirm="حذف الرسالة؟"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Message Modal -->
<div class="modal fade" id="messageModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل الرسالة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="messageBody"></div>
        </div>
    </div>
</div>

<script>
function showMessage(m) {
    var html = '';
    html += '<p><strong>الاسم:</strong> ' + m.name + '</p>';
    if (m.email) html += '<p><strong>البريد:</strong> ' + m.email + '</p>';
    if (m.phone) html += '<p><strong>الهاتف:</strong> ' + m.phone + '</p>';
    html += '<p><strong>الموضوع:</strong> ' + (m.subject || '-') + '</p>';
    html += '<hr><p><strong>الرسالة:</strong></p><p>' + m.message + '</p>';
    html += '<hr><p class="text-muted" style="font-size:0.82rem;">' + m.created_at + '</p>';
    document.getElementById('messageBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('messageModal')).show();
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
