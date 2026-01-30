<?php
/**
 * API для работы с проектами
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/auth.php';
requireAuth();

// CSRF проверка для изменяющих операций
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
}

$db = getDB();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $what_done = trim($_POST['what_done'] ?? '');
            $technologies = trim($_POST['technologies'] ?? '');
            $link = trim($_POST['link'] ?? '');
            $logo_path = trim($_POST['logo_path'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            $website = trim($_POST['website'] ?? '');
            $display_order = intval($_POST['display_order'] ?? 0);
            
            if (empty($title) || empty($description) || empty($link) || empty($logo_path) || empty($tags)) {
                echo json_encode(['success' => false, 'message' => 'Все поля обязательны для заполнения']);
                exit;
            }
            
            $stmt = $db->prepare("INSERT INTO projects (title, subtitle, description, what_done, technologies, link, logo_path, tags, website, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $description, $what_done, $technologies, $link, $logo_path, $tags, $website, $display_order]);
            
            // Создаем PHP файл для проекта, если его нет
            $projectLink = trim($link);
            // Если ссылка без расширения, добавляем .php
            if (!preg_match('/\.(php|html)$/', $projectLink)) {
                $filename = $projectLink . '.php';
            } else {
                $filename = preg_replace('/\.(php|html)$/', '.php', $projectLink);
            }
            
            $filePath = __DIR__ . '/../' . $filename;
            if (!file_exists($filePath)) {
                $template = <<<'PHP'
<?php
/**
 * Универсальный обработчик проекта
 * Загружает проект из БД
 */
require_once __DIR__ . '/config/database.php';

$db = getDB();
$filenameWithoutExt = basename(__FILE__, '.php');
$stmt = $db->prepare("SELECT * FROM projects WHERE link = ? OR link = ? OR link = ? OR link LIKE ?");
$stmt->execute([
    $filenameWithoutExt . '.php',
    $filenameWithoutExt,
    basename(__FILE__),
    '%' . $filenameWithoutExt . '%'
]);
$project = $stmt->fetch();

if ($project) {
    include __DIR__ . '/templates/project_page.php';
} else {
    http_response_code(404);
    include __DIR__ . '/404.php';
}
PHP;
                file_put_contents($filePath, $template);
            }
            
            echo json_encode(['success' => true, 'message' => 'Проект успешно добавлен', 'id' => $db->lastInsertId()]);
            break;
            
        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $what_done = trim($_POST['what_done'] ?? '');
            $technologies = trim($_POST['technologies'] ?? '');
            $link = trim($_POST['link'] ?? '');
            $logo_path = trim($_POST['logo_path'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            $website = trim($_POST['website'] ?? '');
            $display_order = intval($_POST['display_order'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Неверный ID проекта']);
                exit;
            }
            
            if (empty($title) || empty($description) || empty($link) || empty($logo_path) || empty($tags)) {
                echo json_encode(['success' => false, 'message' => 'Все поля обязательны для заполнения']);
                exit;
            }
            
            $stmt = $db->prepare("UPDATE projects SET title = ?, subtitle = ?, description = ?, what_done = ?, technologies = ?, link = ?, logo_path = ?, tags = ?, website = ?, display_order = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$title, $subtitle, $description, $what_done, $technologies, $link, $logo_path, $tags, $website, $display_order, $id]);
            
            // Обновляем PHP файл для проекта, если ссылка изменилась
            $oldStmt = $db->prepare("SELECT link FROM projects WHERE id = ?");
            $oldStmt->execute([$id]);
            $oldProject = $oldStmt->fetch();
            
            // Создаем PHP файл для новой ссылки
            $projectLink = trim($link);
            if (!preg_match('/\.(php|html)$/', $projectLink)) {
                $filename = $projectLink . '.php';
            } else {
                $filename = preg_replace('/\.(php|html)$/', '.php', $projectLink);
            }
            
            $filePath = __DIR__ . '/../' . $filename;
            if (!file_exists($filePath)) {
                $template = <<<'PHP'
<?php
/**
 * Универсальный обработчик проекта
 * Загружает проект из БД
 */
require_once __DIR__ . '/config/database.php';

$db = getDB();
$filenameWithoutExt = basename(__FILE__, '.php');
$stmt = $db->prepare("SELECT * FROM projects WHERE link = ? OR link = ? OR link = ? OR link LIKE ?");
$stmt->execute([
    $filenameWithoutExt . '.php',
    $filenameWithoutExt,
    basename(__FILE__),
    '%' . $filenameWithoutExt . '%'
]);
$project = $stmt->fetch();

if ($project) {
    include __DIR__ . '/templates/project_page.php';
} else {
    http_response_code(404);
    include __DIR__ . '/404.php';
}
PHP;
                file_put_contents($filePath, $template);
            }
            
            echo json_encode(['success' => true, 'message' => 'Проект успешно обновлен']);
            break;
            
        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Неверный ID проекта']);
                exit;
            }
            
            $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Проект успешно удален']);
            break;
            
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Неверный ID проекта']);
                exit;
            }
            
            $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            $project = $stmt->fetch();
            
            if ($project) {
                echo json_encode(['success' => true, 'project' => $project]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Проект не найден']);
            }
            break;
            
        case 'list':
            $projects = $db->query("SELECT * FROM projects ORDER BY display_order ASC, id DESC")->fetchAll();
            echo json_encode(['success' => true, 'projects' => $projects]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
    }
} catch (Exception $e) {
    error_log('Error in projects.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка при выполнении операции']);
}
?>
