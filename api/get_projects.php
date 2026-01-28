<?php
/**
 * Публичный API для получения списка проектов (без авторизации)
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$db = getDB();

try {
    $projects = $db->query("SELECT * FROM projects ORDER BY display_order ASC, id DESC")->fetchAll();
    echo json_encode(['success' => true, 'projects' => $projects]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}
?>
