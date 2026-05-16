<?php
require_once __DIR__ . '/../config/app.php';
header('Content-Type: application/json');

$query = isset($_GET['q']) ? sanitize($_GET['q']) : '';

if (mb_strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'الاستعلام قصير جداً']);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT p.id, p.name_ar, c.name_ar as city_name, s.name_ar as street_name
    FROM pharmacies p
    JOIN cities c ON p.city_id = c.id
    JOIN streets s ON p.street_id = s.id
    WHERE p.status = 'approved'
    AND (p.name_ar LIKE ? OR s.name_ar LIKE ? OR p.phone LIKE ?)
    LIMIT 10
");
$search = '%' . $query . '%';
$stmt->execute([$search, $search, $search]);
$results = $stmt->fetchAll();

echo json_encode(['success' => true, 'data' => $results]);
