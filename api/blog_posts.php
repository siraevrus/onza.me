<?php
/**
 * API для управления статьями блога
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
            $items = $db->query("SELECT * FROM blog_posts ORDER BY display_order ASC, id DESC")->fetchAll();
            echo json_encode(['success' => true, 'posts' => $items]);
            break;

        case 'add':
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            $image_path = trim($_POST['image_path'] ?? '');
            $content = (string)($_POST['content'] ?? '');
            $display_order = intval($_POST['display_order'] ?? 0);

            if ($title === '' || $subtitle === '' || $image_path === '' || trim(strip_tags($content)) === '') {
                echo json_encode(['success' => false, 'message' => 'Заполните превью, заголовок, подзаголовок и контент']);
                exit;
            }

            $stmt = $db->prepare("INSERT INTO blog_posts (title, subtitle, image_path, content, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $image_path, $content, $display_order]);

            echo json_encode(['success' => true, 'message' => 'Статья добавлена', 'id' => $db->lastInsertId()]);
            break;

        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            $image_path = trim($_POST['image_path'] ?? '');
            $content = (string)($_POST['content'] ?? '');
            $display_order = intval($_POST['display_order'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Неверный ID статьи']);
                exit;
            }
            if ($title === '' || $subtitle === '' || $image_path === '' || trim(strip_tags($content)) === '') {
                echo json_encode(['success' => false, 'message' => 'Заполните превью, заголовок, подзаголовок и контент']);
                exit;
            }

            $stmt = $db->prepare("UPDATE blog_posts SET title = ?, subtitle = ?, image_path = ?, content = ?, display_order = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$title, $subtitle, $image_path, $content, $display_order, $id]);

            echo json_encode(['success' => true, 'message' => 'Статья обновлена']);
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Неверный ID статьи']);
                exit;
            }

            $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Статья удалена']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
    }
} catch (PDOException $e) {
    error_log('Database error in blog_posts.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка при работе с базой данных']);
}

