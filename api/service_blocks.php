<?php
/**
 * API для управления блоками услуг
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
            $serviceId = intval($_GET['service_id'] ?? 0);
            if (!$serviceId) {
                echo json_encode(['success' => false, 'message' => 'Не указан ID услуги']);
                exit;
            }
            
            $blocks = $db->prepare("SELECT * FROM service_blocks WHERE service_id = ? ORDER BY display_order ASC, id ASC");
            $blocks->execute([$serviceId]);
            $result = $blocks->fetchAll();
            
            echo json_encode(['success' => true, 'blocks' => $result]);
            break;
            
        case 'add':
            $serviceId = intval($_POST['service_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content'] ?? '';
            $hasBackground = isset($_POST['has_background']) ? intval($_POST['has_background']) : 0;
            $displayOrder = intval($_POST['display_order'] ?? 0);
            
            if (!$serviceId || empty($title) || empty($content)) {
                echo json_encode(['success' => false, 'message' => 'Заполните все обязательные поля']);
                exit;
            }
            
            $stmt = $db->prepare("INSERT INTO service_blocks (service_id, title, content, has_background, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$serviceId, $title, $content, $hasBackground, $displayOrder]);
            
            echo json_encode(['success' => true, 'message' => 'Блок добавлен', 'id' => $db->lastInsertId()]);
            break;
            
        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content'] ?? '';
            $hasBackground = isset($_POST['has_background']) ? intval($_POST['has_background']) : 0;
            $displayOrder = intval($_POST['display_order'] ?? 0);
            
            if (!$id || empty($title) || empty($content)) {
                echo json_encode(['success' => false, 'message' => 'Заполните все обязательные поля']);
                exit;
            }
            
            $stmt = $db->prepare("UPDATE service_blocks SET title = ?, content = ?, has_background = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$title, $content, $hasBackground, $displayOrder, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Блок обновлен']);
            break;
            
        case 'delete':
            $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Не указан ID блока']);
                exit;
            }
            
            $stmt = $db->prepare("DELETE FROM service_blocks WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Блок удален']);
            break;
            
        case 'reorder':
            $items = json_decode($_POST['items'] ?? '[]', true);
            if (empty($items)) {
                echo json_encode(['success' => false, 'message' => 'Нет данных для обновления']);
                exit;
            }
            
            $stmt = $db->prepare("UPDATE service_blocks SET display_order = ? WHERE id = ?");
            foreach ($items as $index => $item) {
                $stmt->execute([$index, $item['id']]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Порядок обновлен']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
    }
} catch (PDOException $e) {
    error_log('Database error in service_blocks.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка при работе с базой данных']);
}
?>
