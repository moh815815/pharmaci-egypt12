<?php
require_once __DIR__ . '/../config/app.php';
$page_title = 'إدارة المدن والمناطق';
include __DIR__ . '/includes/header.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name_ar = sanitize($_POST['name_ar']);
    $name_en = sanitize($_POST['name_en']);
    $governorate_id = (int)$_POST['governorate_id'];
    $stmt = $pdo->prepare("INSERT INTO cities (governorate_id, name_ar, name_en) VALUES (?, ?, ?)");
    $stmt->execute([$governorate_id, $name_ar, $name_en]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم إضافة المدينة'];
    redirect(ADMIN_URL . '/cities.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $name_ar = sanitize($_POST['name_ar']);
    $name_en = sanitize($_POST['name_en']);
    $governorate_id = (int)$_POST['governorate_id'];
    $status = isset($_POST['status']) ? 1 : 0;
    $pdo->prepare("UPDATE cities SET name_ar=?, name_en=?, governorate_id=?, status=? WHERE id=?")
        ->execute([$name_ar, $name_en, $governorate_id, $status, $id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم تحديث المدينة'];
    redirect(ADMIN_URL . '/cities.php');
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM cities WHERE id = ?")->execute([(int)$_GET['delete']]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم حذف المدينة'];
    redirect(ADMIN_URL . '/cities.php');
}

$governorates = $pdo->query("SELECT * FROM governorates WHERE status = 1 ORDER BY name_ar")->fetchAll();
$cities = $pdo->query("
    SELECT c.*, g.name_ar as gov_name
    FROM cities c
    JOIN governorates g ON c.governorate_id = g.id
    ORDER BY g.sort_order, c.name_ar
")->fetchAll();
?>

<div class="admin-header">
    <h4><i class="fas fa-city ms-1"></i> إدارة المدن والمناطق</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus ms-1"></i> إضافة مدينة</button>
</div>

<div class="admin-table">
    <table class="table table-hover">
        <thead>
            <tr><th>#</th><th>المدينة</th><th>المحافظة</th><th>الحالة</th><th>الإجراءات</th></tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($cities as $c): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php _e($c['name_ar']); ?></td>
                <td><?php _e($c['gov_name']); ?></td>
                <td><?php echo $c['status'] ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-secondary">غير نشط</span>'; ?></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick='editCity(<?php echo json_encode($c); ?>)'><i class="fas fa-edit"></i></button>
                    <a href="?delete=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger" data-confirm="حذف؟"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5 class="modal-title">إضافة مدينة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="add" value="1">
                    <div class="mb-3">
                        <label class="form-label">المحافظة</label>
                        <select name="governorate_id" class="form-select" required>
                            <?php foreach ($governorates as $gov): ?>
                                <option value="<?php echo $gov['id']; ?>"><?php _e($gov['name_ar']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الاسم (عربي)</label>
                        <input type="text" name="name_ar" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الاسم (إنجليزي)</label>
                        <input type="text" name="name_en" class="form-control" dir="ltr">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">حفظ</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCity(c) {
    var form = '<form method="POST">';
    form += '<input type="hidden" name="edit" value="1"><input type="hidden" name="id" value="' + c.id + '">';
    form += '<div class="mb-3"><label class="form-label">المحافظة</label><select name="governorate_id" class="form-select">';
    <?php foreach ($governorates as $gov): ?>
        form += '<option value="<?php echo $gov['id']; ?>" ' + (c.governorate_id == <?php echo $gov['id']; ?> ? 'selected' : '') + '><?php _e($gov['name_ar']); ?></option>';
    <?php endforeach; ?>
    form += '</select></div>';
    form += '<div class="mb-3"><label class="form-label">الاسم (عربي)</label><input type="text" name="name_ar" class="form-control" value="' + c.name_ar + '" required></div>';
    form += '<div class="mb-3"><label class="form-label">الاسم (إنجليزي)</label><input type="text" name="name_en" class="form-control" value="' + (c.name_en || '') + '"></div>';
    form += '<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="status" value="1" ' + (c.status ? 'checked' : '') + '><label class="form-check-label">نشط</label></div>';
    form += '<button type="submit" class="btn btn-primary">تحديث</button>';
    form += '</form>';

    var modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = '<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">تعديل مدينة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">' + form + '</div></div></div>';
    document.body.appendChild(modal);
    var bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    modal.addEventListener('hidden.bs.modal', function() { modal.remove(); });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
