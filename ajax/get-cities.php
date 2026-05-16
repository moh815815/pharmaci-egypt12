<?php
require_once __DIR__ . '/../config/app.php';
header('Content-Type: application/json');

if (!isset($_POST['governorate_id']) || !is_numeric($_POST['governorate_id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف المحافظة مطلوب']);
    exit;
}

$cities = getCitiesByGovernorate((int)$_POST['governorate_id']);
echo json_encode(['success' => true, 'data' => $cities]);
