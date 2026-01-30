<?php
/**
 * API для управления вакансиями
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
            $items = $db->query("SELECT * FROM vacancies ORDER BY display_order ASC, id DESC")->fetchAll();
            echo json_encode(['success' => true, 'vacancies' => $items]);
            break;

        case 'add':
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            $display_order = intval($_POST['display_order'] ?? 0);

            if ($title === '' || $subtitle === '') {
                echo json_encode(['success' => false, 'message' => 'Заполните заголовок и подзаголовок']);
                exit;
            }

            $stmt = $db->prepare("INSERT INTO vacancies (title, subtitle, display_order) VALUES (?, ?, ?)");
            $stmt->execute([$title, $subtitle, $display_order]);

            echo json_encode(['success' => true, 'message' => 'Вакансия добавлена', 'id' => $db->lastInsertId()]);
            break;

        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $subtitle = trim($_POST['subtitle'] ?? '');
            $display_order = intval($_POST['display_order'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Неверный ID вакансии']);
                exit;
            }
            if ($title === '' || $subtitle === '') {
                echo json_encode(['success' => false, 'message' => 'Заполните заголовок и подзаголовок']);
                exit;
            }

            $stmt = $db->prepare("UPDATE vacancies SET title = ?, subtitle = ?, display_order = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$title, $subtitle, $display_order, $id]);

            echo json_encode(['success' => true, 'message' => 'Вакансия обновлена']);
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Неверный ID вакансии']);
                exit;
            }

            $stmt = $db->prepare("DELETE FROM vacancies WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Вакансия удалена']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
    }
} catch (PDOException $e) {
    error_log('Database error in vacancies.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка при работе с базой данных']);
}

