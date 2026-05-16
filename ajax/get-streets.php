<?php
require_once __DIR__ . '/../config/app.php';
header('Content-Type: application/json');

if (!isset($_POST['city_id']) || !is_numeric($_POST['city_id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف المدينة مطلوب']);
    exit;
}

$streets = getStreetsByCity((int)$_POST['city_id']);
echo json_encode(['success' => true, 'data' => $streets]);
