<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /mini-erp/views/login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role_id'] != 1) {
        header('Location: /mini-erp/views/dashboard.php');
        exit();
    }
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUsername() {
    return $_SESSION['username'] ?? null;
}

function getUserRole() {
    return $_SESSION['role_id'] ?? null;
}

function logout() {
    session_destroy();
    header('Location: /mini-erp/views/login.php');
    exit();
}
?>
