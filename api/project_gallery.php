<?php
/**
 * API для управления галереей изображений проектов
 */
require_once __DIR__ . '/../config/auth.php';
requireAuth();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}

// CSRF проверка для изменяющих операций
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
}

$db = getDB();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get':
            $projectId = intval($_GET['project_id'] ?? 0);
            if (!$projectId) {
                echo json_encode(['success' => false, 'message' => 'Не указан ID проекта']);
                exit;
            }
            
            $images = $db->prepare("SELECT * FROM project_gallery WHERE project_id = ? ORDER BY display_order ASC, id ASC");
            $images->execute([$projectId]);
            $result = $images->fetchAll();
            
            echo json_encode(['success' => true, 'images' => $result]);
            break;
            
        case 'add':
            $projectId = intval($_POST['project_id'] ?? 0);
            $imagePath = trim($_POST['image_path'] ?? '');
            $displayOrder = intval($_POST['display_order'] ?? 0);
            
            if (!$projectId || empty($imagePath)) {
                echo json_encode(['success' => false, 'message' => 'Заполните все обязательные поля']);
                exit;
            }
            
            $stmt = $db->prepare("INSERT INTO project_gallery (project_id, image_path, display_order) VALUES (?, ?, ?)");
            $stmt->execute([$projectId, $imagePath, $displayOrder]);
            
            echo json_encode(['success' => true, 'message' => 'Изображение добавлено', 'id' => $db->lastInsertId()]);
            break;
            
        case 'delete':
            $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Не указан ID изображения']);
                exit;
            }
            
            $stmt = $db->prepare("DELETE FROM project_gallery WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Изображение удалено']);
            break;
            
        case 'reorder':
            $items = json_decode($_POST['items'] ?? '[]', true);
            if (empty($items)) {
                echo json_encode(['success' => false, 'message' => 'Нет данных для обновления']);
                exit;
            }
            
            $stmt = $db->prepare("UPDATE project_gallery SET display_order = ? WHERE id = ?");
            foreach ($items as $index => $item) {
                $stmt->execute([$index, $item['id']]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Порядок обновлен']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
    }
} catch (PDOException $e) {
    error_log('Database error in project_gallery.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка при работе с базой данных']);
}
?>
