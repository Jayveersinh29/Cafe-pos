<?php
/**
 * Authentication / session helpers.
 * Include this at the top of every protected page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Redirect to login if not authenticated. Call at the top of protected pages. */
function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . base_url('login.php'));
        exit;
    }
}

/** Redirect non-admins away from admin-only pages. */
function require_admin() {
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: ' . base_url('dashboard.php?error=forbidden'));
        exit;
    }
}

function current_user() {
    return [
        'id'        => $_SESSION['user_id'] ?? null,
        'username'  => $_SESSION['username'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'role'      => $_SESSION['role'] ?? null,
    ];
}

function is_admin() {
    return ($_SESSION['role'] ?? '') === 'admin';
}

/**
 * Builds a path relative to the app root regardless of which
 * subfolder (pages/, api/) the including script lives in.
 */
function base_url($path = '') {
    // APP_ROOT is defined by whichever entry file included this.
    $root = defined('APP_ROOT') ? APP_ROOT : '';
    return $root . $path;
}
