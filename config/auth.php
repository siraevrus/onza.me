<?php
/**
 * Функции для работы с авторизацией
 */

session_start();

require_once __DIR__ . '/database.php';

/**
 * Генерация CSRF токена
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Проверка CSRF токена
 */
function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Проверка CSRF с выводом ошибки JSON
 */
function requireCsrf() {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!verifyCsrfToken($token)) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Недействительный CSRF токен. Обновите страницу.']);
        exit;
    }
}

/**
 * Вывод скрытого поля с CSRF токеном
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '" />';
}

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
 * Проверка rate limiting для защиты от брутфорса
 */
function checkLoginRateLimit($ip) {
    $db = getDB();
    
    // Создаем таблицу для хранения попыток, если её нет
    $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip TEXT NOT NULL,
        attempt_time INTEGER NOT NULL
    )");
    
    // Удаляем старые записи (старше 15 минут)
    $db->exec("DELETE FROM login_attempts WHERE attempt_time < " . (time() - 900));
    
    // Считаем попытки за последние 15 минут
    $stmt = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempt_time > ?");
    $stmt->execute([$ip, time() - 900]);
    $attempts = $stmt->fetchColumn();
    
    // Максимум 5 попыток за 15 минут
    return $attempts < 5;
}

/**
 * Запись неудачной попытки входа
 */
function recordLoginAttempt($ip) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO login_attempts (ip, attempt_time) VALUES (?, ?)");
    $stmt->execute([$ip, time()]);
}

/**
 * Очистка попыток входа после успешной авторизации
 */
function clearLoginAttempts($ip) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip = ?");
    $stmt->execute([$ip]);
}

/**
 * Вход пользователя
 */
function login($username, $password) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Проверяем rate limiting
    if (!checkLoginRateLimit($ip)) {
        return ['success' => false, 'message' => 'Слишком много попыток входа. Попробуйте через 15 минут.'];
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        clearLoginAttempts($ip);
        return ['success' => true];
    }
    
    recordLoginAttempt($ip);
    return ['success' => false, 'message' => 'Неверный логин или пароль'];
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
