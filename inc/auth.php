<?php
$user = $_SESSION['user'] ?? $_POST['user'] ?? '';
$pass = $_SESSION['pass'] ?? $_POST['pass'] ?? '';
$base = trim($_SESSION['url'] ?? $_POST['url'] ?? '');

if (!preg_match('#^https?://#', $base)) {
    $base = 'https://' . $base;
}

