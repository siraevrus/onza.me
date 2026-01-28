<?php
/**
 * Функции для работы с авторизацией
 */

session_start();

require_once __DIR__ . '/database.php';

/**
 * Проверка, авторизован ли пользователь
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Проверка авторизации с редиректом на страницу входа
 */
function requireAuth() {
    if (!isAuthenticated()) {
        $loginUrl = dirname($_SERVER['PHP_SELF']) === '/' ? '/login.php' : dirname($_SERVER['PHP_SELF']) . '/login.php';
        header('Location: ' . $loginUrl);
        exit;
    }
}

/**
 * Вход пользователя
 */
function login($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    
    return false;
}

/**
 * Выход пользователя
 */
function logout() {
    session_destroy();
    $loginUrl = dirname($_SERVER['PHP_SELF']) === '/' ? '/login.php' : dirname($_SERVER['PHP_SELF']) . '/login.php';
    header('Location: ' . $loginUrl);
    exit;
}

/**
 * Получить текущего пользователя
 */
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username']
    ];
}
?>
