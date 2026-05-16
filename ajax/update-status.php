<?php
require_once __DIR__ . '/../config/app.php';
header('Content-Type: application/json');

requireAdminLogin();

if (!isset($_POST['pharmacy_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير كاملة']);
    exit;
}

$pharmacy_id = (int)$_POST['pharmacy_id'];
$status = sanitize($_POST['status']);

$allowed_statuses = ['pending', 'approved', 'rejected', 'suspended'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'حالة غير صالحة']);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("UPDATE pharmacies SET status = ? WHERE id = ?");
$stmt->execute([$status, $pharmacy_id]);

logActivity($_SESSION['admin_id'], 'تغيير حالة الصيدلية', "تم تغيير حالة الصيدلية رقم $pharmacy_id إلى $status");

echo json_encode(['success' => true, 'message' => 'تم تحديث الحالة بنجاح']);
