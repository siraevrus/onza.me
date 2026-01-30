<?php
/**
 * Страница: service-support
 * Загружает услугу из БД
 */
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$slug = 'service-support';

// Ищем услугу по slug
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
        // Если таблицы еще нет или ошибка, создаем пустой массив
        $blocks = [];
    }
    
    // Убеждаемся, что переменная определена
    if (!isset($blocks)) {
        $blocks = [];
    }
    
    include __DIR__ . '/../templates/service_page.php';
} else {
    // Если услуга не найдена в БД, пробуем загрузить статический файл
    $staticFile = __DIR__ . '/../service-support.html';
    if (file_exists($staticFile)) {
        include $staticFile;
    } else {
        http_response_code(404);
        include __DIR__ . '/../404.php';
    }
}