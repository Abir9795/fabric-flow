<?php
require_once __DIR__ . '/includes/bootstrap.php';
redirect(!empty($_SESSION['user_id']) ? 'admin_dashboard.php' : 'homepage.php');
