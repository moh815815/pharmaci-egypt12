<?php
require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'database.php';

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim((string)$input)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        redirect(ADMIN_URL . '/login.php');
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/auth/login.php');
    }
}

function _e($text) {
    echo htmlspecialchars((string)($text ?? ''), ENT_QUOTES, 'UTF-8');
}

function dbSafe(callable $callback, $default = []) {
    $pdo = getDB();
    if (!$pdo) return $default;
    try { return $callback($pdo); }
    catch (Exception $e) { error_log($e->getMessage()); return $default; }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return dbSafe(function($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch() ?: null;
    }, null);
}

function getCurrentAdmin() {
    if (!isAdminLoggedIn()) return null;
    return dbSafe(function($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch() ?: null;
    }, null);
}

function getGovernorates() {
    return dbSafe(function($pdo) {
        return $pdo->query("SELECT * FROM governorates WHERE status = 1 ORDER BY sort_order ASC, name_ar ASC")->fetchAll();
    });
}

function getCitiesByGovernorate($governorate_id) {
    return dbSafe(function($pdo) use ($governorate_id) {
        $stmt = $pdo->prepare("SELECT * FROM cities WHERE governorate_id = ? AND status = 1 ORDER BY name_ar ASC");
        $stmt->execute([$governorate_id]);
        return $stmt->fetchAll();
    });
}

function getStreetsByCity($city_id) {
    return dbSafe(function($pdo) use ($city_id) {
        $stmt = $pdo->prepare("SELECT * FROM streets WHERE city_id = ? AND status = 1 ORDER BY name_ar ASC");
        $stmt->execute([$city_id]);
        return $stmt->fetchAll();
    });
}

function getTiers() {
    return dbSafe(function($pdo) {
        return $pdo->query("SELECT * FROM pharmacy_tiers ORDER BY sort_order ASC")->fetchAll();
    });
}

function getTierById($id) {
    return dbSafe(function($pdo) use ($id) {
        $stmt = $pdo->prepare("SELECT * FROM pharmacy_tiers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }, null);
}

function getTierBySlug($slug) {
    return dbSafe(function($pdo) use ($slug) {
        $stmt = $pdo->prepare("SELECT * FROM pharmacy_tiers WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }, null);
}

function getPharmacy($id) {
    return dbSafe(function($pdo) use ($id) {
        $stmt = $pdo->prepare("
            SELECT p.*, t.name_ar as tier_name, t.slug as tier_slug,
                   g.name_ar as governorate_name, c.name_ar as city_name, s.name_ar as street_name
            FROM pharmacies p
            JOIN pharmacy_tiers t ON p.tier_id = t.id
            JOIN governorates g ON p.governorate_id = g.id
            JOIN cities c ON p.city_id = c.id
            JOIN streets s ON p.street_id = s.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }, null);
}

function getPharmacyPhotos($pharmacy_id) {
    return dbSafe(function($pdo) use ($pharmacy_id) {
        $stmt = $pdo->prepare("SELECT * FROM pharmacy_photos WHERE pharmacy_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$pharmacy_id]);
        return $stmt->fetchAll();
    });
}

function uploadFile($file, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp']) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'خطأ في رفع الملف'];
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'حجم الملف كبير جداً (الحد الأقصى 5 ميجابايت)'];
    }
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_types)) {
        return ['success' => false, 'message' => 'نوع الملف غير مسموح به'];
    }
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $new_name = uniqid('phar_') . '_' . time() . '.' . $file_ext;
    $target_path = $target_dir . $new_name;
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => true, 'filename' => $new_name, 'path' => $target_path];
    }
    return ['success' => false, 'message' => 'فشل في حفظ الملف'];
}

function uploadMultipleFiles($files, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp']) {
    $uploaded = [];
    if (!isset($files['name']) || !is_array($files['name'])) return $uploaded;
    foreach ($files['name'] as $key => $name) {
        if ($files['error'][$key] === UPLOAD_ERR_OK) {
            $file = [
                'name'     => $files['name'][$key],
                'type'     => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error'    => $files['error'][$key],
                'size'     => $files['size'][$key],
            ];
            $result = uploadFile($file, $target_dir, $allowed_types);
            if ($result['success']) $uploaded[] = $result['filename'];
        }
    }
    return $uploaded;
}

function formatDate($date, $format = 'Y/m/d') {
    if (!$date) return '-';
    $timestamp = is_numeric($date) ? (int)$date : strtotime($date);
    if (!$timestamp) return '-';
    $months = [
        'January' => 'يناير', 'February' => 'فبراير', 'March' => 'مارس',
        'April' => 'أبريل', 'May' => 'مايو', 'June' => 'يونيو',
        'July' => 'يوليو', 'August' => 'أغسطس', 'September' => 'سبتمبر',
        'October' => 'أكتوبر', 'November' => 'نوفمبر', 'December' => 'ديسمبر',
    ];
    $days = [
        'Saturday' => 'السبت', 'Sunday' => 'الأحد', 'Monday' => 'الإثنين',
        'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 'Friday' => 'الجمعة',
    ];
    $formatted = date($format, $timestamp);
    $formatted = str_replace(array_keys($months), array_values($months), $formatted);
    $formatted = str_replace(array_keys($days), array_values($days), $formatted);
    return $formatted;
}

function getSetting($key, $default = '') {
    return dbSafe(function($pdo) use ($key, $default) {
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE key_name = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['value'] : $default;
    }, $default);
}

function getStatusBadge($status) {
    switch ($status) {
        case 'approved':   return '<span class="badge bg-success">مقبول</span>';
        case 'pending':    return '<span class="badge bg-warning text-dark">قيد المراجعة</span>';
        case 'rejected':   return '<span class="badge bg-danger">مرفوض</span>';
        case 'suspended':  return '<span class="badge bg-secondary">موقوف</span>';
        default:           return '<span class="badge bg-secondary">' . sanitize($status) . '</span>';
    }
}

function getTierBadge($tier_slug, $tier_name) {
    switch ($tier_slug) {
        case 'large':  return '<span class="badge tier-gold">' . sanitize($tier_name ?? 'ذهبية') . '</span>';
        case 'medium': return '<span class="badge tier-silver">' . sanitize($tier_name ?? 'فضية') . '</span>';
        case 'basic':  return '<span class="badge tier-basic">' . sanitize($tier_name ?? 'أساسية') . '</span>';
        default:       return '<span class="badge bg-secondary">' . sanitize($tier_name ?? '') . '</span>';
    }
}

function searchPharmacies($params = []) {
    return dbSafe(function($pdo) use ($params) {
        $sql = "
            SELECT p.*, t.name_ar as tier_name, t.slug as tier_slug,
                   g.name_ar as governorate_name, c.name_ar as city_name, s.name_ar as street_name
            FROM pharmacies p
            JOIN pharmacy_tiers t ON p.tier_id = t.id
            JOIN governorates g ON p.governorate_id = g.id
            JOIN cities c ON p.city_id = c.id
            JOIN streets s ON p.street_id = s.id
            WHERE p.status = 'approved'
        ";
        $binds = [];

        if (!empty($params['search'])) {
            $search = '%' . $params['search'] . '%';
            $sql .= " AND (p.name_ar LIKE ? OR s.name_ar LIKE ? OR p.phone LIKE ?)";
            $binds = array_merge($binds, [$search, $search, $search]);
        }
        if (!empty($params['governorate_id'])) { $sql .= " AND p.governorate_id = ?"; $binds[] = $params['governorate_id']; }
        if (!empty($params['city_id'])) { $sql .= " AND p.city_id = ?"; $binds[] = $params['city_id']; }
        if (!empty($params['street_id'])) { $sql .= " AND p.street_id = ?"; $binds[] = $params['street_id']; }
        if (!empty($params['tier_id'])) { $sql .= " AND p.tier_id = ?"; $binds[] = $params['tier_id']; }
        if (!empty($params['is_24hours'])) { $sql .= " AND p.is_24hours = 1"; }
        if (!empty($params['has_delivery'])) { $sql .= " AND p.has_delivery = 1"; }

        $sql .= " ORDER BY t.sort_order ASC, p.name_ar ASC";
        if (!empty($params['limit'])) { $sql .= " LIMIT ?"; $binds[] = (int)$params['limit']; }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($binds);
        return $stmt->fetchAll();
    });
}

function getDashboardStats() {
    return dbSafe(function($pdo) {
        $stats = [];
        $stats['total_pharmacies'] = $pdo->query("SELECT COUNT(*) FROM pharmacies")->fetchColumn();
        $stats['pending_pharmacies'] = $pdo->query("SELECT COUNT(*) FROM pharmacies WHERE status = 'pending'")->fetchColumn();
        $stats['approved_pharmacies'] = $pdo->query("SELECT COUNT(*) FROM pharmacies WHERE status = 'approved'")->fetchColumn();
        $stats['total_governorates'] = $pdo->query("SELECT COUNT(*) FROM governorates WHERE status = 1")->fetchColumn();
        $stats['total_cities'] = $pdo->query("SELECT COUNT(*) FROM cities WHERE status = 1")->fetchColumn();
        $stats['total_streets'] = $pdo->query("SELECT COUNT(*) FROM streets WHERE status = 1")->fetchColumn();
        $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['today_pharmacies'] = $pdo->query("SELECT COUNT(*) FROM pharmacies WHERE DATE(created_at) = CURDATE()")->fetchColumn();

        $stmt = $pdo->query("
            SELECT t.name_ar, COUNT(p.id) as total
            FROM pharmacy_tiers t LEFT JOIN pharmacies p ON p.tier_id = t.id
            GROUP BY t.id, t.name_ar ORDER BY t.sort_order
        ");
        $stats['by_tier'] = $stmt->fetchAll();
        return $stats;
    }, [
        'total_pharmacies' => 0, 'pending_pharmacies' => 0, 'approved_pharmacies' => 0,
        'total_governorates' => 0, 'total_cities' => 0, 'total_streets' => 0,
        'total_users' => 0, 'today_pharmacies' => 0, 'by_tier' => []
    ]);
}

function countPharmaciesByStatus($status = 'pending') {
    return dbSafe(function($pdo) use ($status) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pharmacies WHERE status = ?");
        $stmt->execute([$status]);
        return $stmt->fetchColumn();
    }, 0);
}

function logActivity($admin_id, $action, $details = null) {
    dbSafe(function($pdo) use ($admin_id, $action, $details) {
        $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$admin_id, $action, $details, $_SERVER['REMOTE_ADDR'] ?? '']);
    });
}

function ensureLogsTable() {
    dbSafe(function($pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `admin_logs` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `admin_id` INT UNSIGNED DEFAULT NULL,
                `action` VARCHAR(255) NOT NULL,
                `details` TEXT DEFAULT NULL,
                `ip_address` VARCHAR(45) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    });
}

function timeAgo($timestamp) {
    if (!$timestamp) return '-';
    $time = is_numeric($timestamp) ? (int)$timestamp : strtotime($timestamp);
    if (!$time) return '-';
    $diff = time() - $time;
    $intervals = [
        31536000 => 'سنة', 2592000 => 'شهر', 604800 => 'أسبوع',
        86400 => 'يوم', 3600 => 'ساعة', 60 => 'دقيقة', 1 => 'ثانية',
    ];
    foreach ($intervals as $seconds => $label) {
        $count = floor($diff / $seconds);
        if ($count >= 1) return "منذ $count $label";
    }
    return 'الآن';
}

function pagination($current_page, $total_pages, $url_pattern) {
    if ($total_pages <= 1) return '';
    $html = '<nav aria-label="التنقل بين الصفحات"><ul class="pagination justify-content-center">';
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . str_replace('{page}', $current_page - 1, $url_pattern) . '">« السابق</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">« السابق</span></li>';
    }
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . str_replace('{page}', $i, $url_pattern) . '">' . $i . '</a></li>';
        }
    }
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . str_replace('{page}', $current_page + 1, $url_pattern) . '">التالي »</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">التالي »</span></li>';
    }
    $html .= '</ul></nav>';
    return $html;
}
