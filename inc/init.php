<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/functions.php';
