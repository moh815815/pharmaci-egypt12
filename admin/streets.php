<?php
require_once __DIR__ . '/../config/app.php';
$page_title = 'إدارة الشوارع الرئيسية';
include __DIR__ . '/includes/header.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name_ar = sanitize($_POST['name_ar']);
    $name_en = sanitize($_POST['name_en']);
    $city_id = (int)$_POST['city_id'];
    $stmt = $pdo->prepare("INSERT INTO streets (city_id, name_ar, name_en) VALUES (?, ?, ?)");
    $stmt->execute([$city_id, $name_ar, $name_en]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم إضافة الشارع'];
    redirect(ADMIN_URL . '/streets.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $name_ar = sanitize($_POST['name_ar']);
    $name_en = sanitize($_POST['name_en']);
    $city_id = (int)$_POST['city_id'];
    $status = isset($_POST['status']) ? 1 : 0;
    $pdo->prepare("UPDATE streets SET name_ar=?, name_en=?, city_id=?, status=? WHERE id=?")
        ->execute([$name_ar, $name_en, $city_id, $status, $id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم تحديث الشارع'];
    redirect(ADMIN_URL . '/streets.php');
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM streets WHERE id = ?")->execute([(int)$_GET['delete']]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم حذف الشارع'];
    redirect(ADMIN_URL . '/streets.php');
}

$governorates = $pdo->query("SELECT * FROM governorates WHERE status = 1 ORDER BY name_ar")->fetchAll();
$streets = $pdo->query("
    SELECT s.*, c.name_ar as city_name, g.name_ar as gov_name
    FROM streets s
    JOIN cities c ON s.city_id = c.id
    JOIN governorates g ON c.governorate_id = g.id
    ORDER BY g.name_ar, c.name_ar, s.name_ar
")->fetchAll();
?>

<div class="admin-header">
    <h4><i class="fas fa-road ms-1"></i> إدارة الشوارع الرئيسية</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus ms-1"></i> إضافة شارع</button>
</div>

<div class="admin-table">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr><th>#</th><th>الشارع</th><th>المدينة</th><th>المحافظة</th><th>الحالة</th><th>الإجراءات</th></tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($streets as $s): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php _e($s['name_ar']); ?></td>
                    <td><?php _e($s['city_name']); ?></td>
                    <td><?php _e($s['gov_name']); ?></td>
                    <td><?php echo $s['status'] ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-secondary">غير نشط</span>'; ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick='editStreet(<?php echo json_encode($s); ?>)'><i class="fas fa-edit"></i></button>
                        <a href="?delete=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-danger" data-confirm="حذف الشارع؟"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5 class="modal-title">إضافة شارع</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="add" value="1">
                    <div class="mb-3">
                        <label class="form-label">المحافظة</label>
                        <select id="addGovernorate" class="form-select" onchange="loadCities(this.value, 'addCity')">
                            <option value="">-- اختر --</option>
                            <?php foreach ($governorates as $gov): ?>
                                <option value="<?php echo $gov['id']; ?>"><?php _e($gov['name_ar']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المدينة</label>
                        <select name="city_id" id="addCity" class="form-select" required>
                            <option value="">-- اختر المحافظة أولاً --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">اسم الشارع (عربي)</label>
                        <input type="text" name="name_ar" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">اسم الشارع (إنجليزي)</label>
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
function loadCities(govId, targetId) {
    if (!govId) { document.getElementById(targetId).innerHTML = '<option value="">-- اختر المحافظة أولاً --</option>'; return; }
    $.ajax({
        url: BASE_URL + '/ajax/get-cities.php',
        type: 'POST',
        data: { governorate_id: govId },
        dataType: 'json',
        success: function(resp) {
            var sel = document.getElementById(targetId);
            sel.innerHTML = '<option value="">-- اختر المدينة --</option>';
            if (resp.success) {
                resp.data.forEach(function(c) { sel.innerHTML += '<option value="' + c.id + '">' + c.name_ar + '</option>'; });
            }
        }
    });
}

function editStreet(s) {
    var govId = <?php echo json_encode($governorates); ?>;

    var govSelect = '<select class="form-select" onchange="loadCities(this.value, \'editCity' + s.id + '\')">';
    govSelect += '<option value="">-- اختر --</option>';
    govId.forEach(function(g) {
        govSelect += '<option value="' + g.id + '">' + g.name_ar + '</option>';
    });
    govSelect += '</select>';

    var form = '<form method="POST">';
    form += '<input type="hidden" name="edit" value="1"><input type="hidden" name="id" value="' + s.id + '">';
    form += '<div class="mb-3"><label class="form-label">المحافظة</label>' + govSelect + '</div>';
    form += '<div class="mb-3"><label class="form-label">المدينة</label><select name="city_id" id="editCity' + s.id + '" class="form-select"></select></div>';
    form += '<div class="mb-3"><label class="form-label">اسم الشارع (عربي)</label><input type="text" name="name_ar" class="form-control" value="' + s.name_ar + '" required></div>';
    form += '<div class="mb-3"><label class="form-label">اسم الشارع (إنجليزي)</label><input type="text" name="name_en" class="form-control" value="' + (s.name_en || '') + '"></div>';
    form += '<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="status" value="1" ' + (s.status ? 'checked' : '') + '><label class="form-check-label">نشط</label></div>';
    form += '<button type="submit" class="btn btn-primary">تحديث</button></form>';

    var modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = '<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">تعديل شارع</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">' + form + '</div></div></div>';
    document.body.appendChild(modal);
    var bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    modal.addEventListener('hidden.bs.modal', function() { modal.remove(); });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
