<?php
/**
 * Универсальный обработчик для всех услуг
 * Используется когда файл услуги не существует
 */
require_once __DIR__ . '/config/database.php';

// Получаем имя файла из запроса
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Обрабатываем пути вида /services/232323.php
if (preg_match('#^/services/([^/]+)$#', $path, $matches)) {
    $filename = $matches[1];
} else {
    $filename = basename($path);
}
$slug = pathinfo($filename, PATHINFO_FILENAME);

// Также проверяем параметр file из .htaccess
if (isset($_GET['file'])) {
    $slug = pathinfo($_GET['file'], PATHINFO_FILENAME);
}

$db = getDB();

// Ищем услугу по slug
try {
    $stmt = $db->prepare("SELECT * FROM services WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    $service = $stmt->fetch();
    
    if ($service) {
        // Загружаем блоки услуги
        try {
            $blocksStmt = $db->prepare("SELECT * FROM service_blocks WHERE service_id = ? ORDER BY display_order ASC, id ASC");
            $blocksStmt->execute([$service['id']]);
            $blocks = $blocksStmt->fetchAll();
        } catch (Exception $e) {
            $blocks = [];
        }
        
        // Убеждаемся, что переменная определена
        if (!isset($blocks)) {
            $blocks = [];
        }
        
        include __DIR__ . '/templates/service_page.php';
    } else {
        // Услуга не найдена - показываем 404
        http_response_code(404);
        include __DIR__ . '/404.php';
    }
} catch (Exception $e) {
    // Ошибка БД - показываем 404
    http_response_code(404);
    include __DIR__ . '/404.php';
}
?>
