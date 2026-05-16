<?php
require_once __DIR__ . '/../config/app.php';
$page_title = 'إدارة المحافظات';
include __DIR__ . '/includes/header.php';

$pdo = getDB();

// Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name_ar = sanitize($_POST['name_ar']);
    $name_en = sanitize($_POST['name_en']);
    $sort_order = (int)$_POST['sort_order'];
    $stmt = $pdo->prepare("INSERT INTO governorates (name_ar, name_en, sort_order) VALUES (?, ?, ?)");
    $stmt->execute([$name_ar, $name_en, $sort_order]);
    logActivity($_SESSION['admin_id'], 'إضافة محافظة', "تم إضافة محافظة: $name_ar");
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم إضافة المحافظة بنجاح'];
    redirect(ADMIN_URL . '/governorates.php');
}

// Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $name_ar = sanitize($_POST['name_ar']);
    $name_en = sanitize($_POST['name_en']);
    $sort_order = (int)$_POST['sort_order'];
    $status = isset($_POST['status']) ? 1 : 0;
    $pdo->prepare("UPDATE governorates SET name_ar=?, name_en=?, sort_order=?, status=? WHERE id=?")
        ->execute([$name_ar, $name_en, $sort_order, $status, $id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم تحديث المحافظة'];
    redirect(ADMIN_URL . '/governorates.php');
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM governorates WHERE id = ?")->execute([$id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم حذف المحافظة'];
    redirect(ADMIN_URL . '/governorates.php');
}

$governorates = $pdo->query("SELECT * FROM governorates ORDER BY sort_order ASC, name_ar ASC")->fetchAll();
?>

<div class="admin-header">
    <h4><i class="fas fa-map ms-1"></i> إدارة المحافظات</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus ms-1"></i> إضافة محافظة</button>
</div>

<div class="admin-table">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم (عربي)</th>
                <th>الاسم (إنجليزي)</th>
                <th>الترتيب</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($governorates as $gov): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php _e($gov['name_ar']); ?></td>
                <td><?php _e($gov['name_en'] ?: '-'); ?></td>
                <td><?php echo $gov['sort_order']; ?></td>
                <td><?php echo $gov['status'] ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-secondary">غير نشط</span>'; ?></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="editGov(<?php echo $gov['id']; ?>, '<?php _e($gov['name_ar']); ?>', '<?php _e($gov['name_en']); ?>', <?php echo $gov['sort_order']; ?>, <?php echo $gov['status']; ?>)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <a href="?delete=<?php echo $gov['id']; ?>" class="btn btn-sm btn-outline-danger" data-confirm="حذف المحافظة؟"><i class="fas fa-trash"></i></a>
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
                <div class="modal-header"><h5 class="modal-title">إضافة محافظة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="add" value="1">
                    <div class="mb-3">
                        <label class="form-label">الاسم (عربي)</label>
                        <input type="text" name="name_ar" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الاسم (إنجليزي)</label>
                        <input type="text" name="name_en" class="form-control" dir="ltr">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الترتيب</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save ms-1"></i> حفظ</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5 class="modal-title">تعديل محافظة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="edit" value="1">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label">الاسم (عربي)</label>
                        <input type="text" name="name_ar" id="editNameAr" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الاسم (إنجليزي)</label>
                        <input type="text" name="name_en" id="editNameEn" class="form-control" dir="ltr">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الترتيب</label>
                        <input type="number" name="sort_order" id="editSortOrder" class="form-control" value="0">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" id="editStatus" value="1">
                        <label class="form-check-label">نشط</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save ms-1"></i> تحديث</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editGov(id, nameAr, nameEn, sortOrder, status) {
    document.getElementById('editId').value = id;
    document.getElementById('editNameAr').value = nameAr;
    document.getElementById('editNameEn').value = nameEn;
    document.getElementById('editSortOrder').value = sortOrder;
    document.getElementById('editStatus').checked = status == 1;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
