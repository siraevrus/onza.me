<?php
/**
 * API для управления страницами (CMS)
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
        case 'get':
            $slug = $_GET['slug'] ?? '';
            if ($slug) {
                $stmt = $db->prepare("SELECT * FROM pages WHERE slug = ?");
                $stmt->execute([$slug]);
                $page = $stmt->fetch();
                if ($page) {
                    echo json_encode(['success' => true, 'page' => $page]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Страница не найдена']);
                }
            } else {
                $pages = $db->query("SELECT id, slug, title, is_active, created_at, updated_at FROM pages ORDER BY slug ASC")->fetchAll();
                echo json_encode(['success' => true, 'pages' => $pages]);
            }
            break;
            
        case 'add':
            $slug = trim($_POST['slug'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $meta_description = trim($_POST['meta_description'] ?? '');
            $content = $_POST['content'] ?? '';
            
            if (empty($slug) || empty($title) || empty($content)) {
                echo json_encode(['success' => false, 'message' => 'Заполните все обязательные поля']);
                exit;
            }
            
            // Проверяем уникальность slug
            $stmt = $db->prepare("SELECT COUNT(*) FROM pages WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Страница с таким URL уже существует']);
                exit;
            }
            
            $stmt = $db->prepare("INSERT INTO pages (slug, title, meta_description, content) VALUES (?, ?, ?, ?)");
            $stmt->execute([$slug, $title, $meta_description, $content]);
            
            echo json_encode(['success' => true, 'message' => 'Страница успешно создана', 'id' => $db->lastInsertId()]);
            break;
            
        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            $slug = trim($_POST['slug'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $meta_description = trim($_POST['meta_description'] ?? '');
            $content = $_POST['content'] ?? '';
            
            if (!$id || empty($slug) || empty($title) || empty($content)) {
                echo json_encode(['success' => false, 'message' => 'Заполните все обязательные поля']);
                exit;
            }
            
            // Проверяем уникальность slug (исключая текущую страницу)
            $stmt = $db->prepare("SELECT COUNT(*) FROM pages WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $id]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Страница с таким URL уже существует']);
                exit;
            }
            
            $stmt = $db->prepare("UPDATE pages SET slug = ?, title = ?, meta_description = ?, content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$slug, $title, $meta_description, $content, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Страница успешно обновлена']);
            break;
            
        case 'delete':
            $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Не указан ID страницы']);
                exit;
            }
            
            $stmt = $db->prepare("DELETE FROM pages WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Страница успешно удалена']);
            break;
            
        case 'toggle':
            $id = intval($_POST['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Не указан ID страницы']);
                exit;
            }
            
            $stmt = $db->prepare("UPDATE pages SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Статус страницы изменен']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
    }
} catch (PDOException $e) {
    error_log('Database error in pages.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка при работе с базой данных']);
}
?>
