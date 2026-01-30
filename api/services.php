<?php
/**
 * API для управления услугами
 */
require_once __DIR__ . '/../config/auth.php';
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
        case 'list':
            $items = $db->query("SELECT * FROM services ORDER BY display_order ASC, id DESC")->fetchAll();
            echo json_encode(['success' => true, 'services' => $items]);
            break;

        case 'add':
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            $detail_subtitle = trim($_POST['detail_subtitle'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $display_order = intval($_POST['display_order'] ?? 0);

            if ($title === '' || $subtitle === '' || $slug === '') {
                echo json_encode(['success' => false, 'message' => 'Заполните заголовок, подзаголовок и slug']);
                exit;
            }

            // Проверяем уникальность slug
            $stmt = $db->prepare("SELECT COUNT(*) FROM services WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Услуга с таким slug уже существует']);
                exit;
            }

            $stmt = $db->prepare("INSERT INTO services (title, subtitle, detail_subtitle, slug, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $detail_subtitle, $slug, $display_order]);

            echo json_encode(['success' => true, 'message' => 'Услуга добавлена', 'id' => $db->lastInsertId()]);
            break;

        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            $detail_subtitle = trim($_POST['detail_subtitle'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $display_order = intval($_POST['display_order'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Неверный ID услуги']);
                exit;
            }
            if ($title === '' || $subtitle === '' || $slug === '') {
                echo json_encode(['success' => false, 'message' => 'Заполните заголовок, подзаголовок и slug']);
                exit;
            }

            // Проверяем уникальность slug (исключая текущую услугу)
            $stmt = $db->prepare("SELECT COUNT(*) FROM services WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $id]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Услуга с таким slug уже существует']);
                exit;
            }

            $stmt = $db->prepare("UPDATE services SET title = ?, subtitle = ?, detail_subtitle = ?, slug = ?, display_order = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$title, $subtitle, $detail_subtitle, $slug, $display_order, $id]);

            echo json_encode(['success' => true, 'message' => 'Услуга обновлена']);
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Неверный ID услуги']);
                exit;
            }

            $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Услуга удалена']);
            break;

        case 'get':
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Неверный ID услуги']);
                exit;
            }

            $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
            $stmt->execute([$id]);
            $service = $stmt->fetch();

            if ($service) {
                echo json_encode(['success' => true, 'service' => $service]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Услуга не найдена']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
    }
} catch (PDOException $e) {
    error_log('Database error in services.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка при работе с базой данных']);
}
