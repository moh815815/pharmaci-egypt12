<?php
require_once __DIR__ . '/../config/app.php';
unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_role']);
redirect(ADMIN_URL . '/login.php');
