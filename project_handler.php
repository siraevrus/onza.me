<?php
/**
 * Универсальный обработчик для всех проектов
 * Используется когда файл проекта не существует
 */
require_once __DIR__ . '/config/database.php';

// Получаем имя файла из запроса
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$filename = basename($path);
$filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

// Также проверяем параметр file из .htaccess
if (isset($_GET['file'])) {
    $filenameWithoutExt = pathinfo($_GET['file'], PATHINFO_FILENAME);
}

$db = getDB();

// Ищем проект по ссылке (может быть с расширением или без)
$projectLink = $filenameWithoutExt . '.php';
$stmt = $db->prepare("SELECT * FROM projects WHERE link = ? OR link = ? OR link LIKE ?");
$stmt->execute([$projectLink, $filenameWithoutExt, '%' . $filenameWithoutExt . '%']);
$project = $stmt->fetch();

if ($project) {
    // Найден проект в БД - показываем страницу проекта
    include __DIR__ . '/templates/project_page.php';
} else {
    // Проект не найден - показываем 404
    http_response_code(404);
    include __DIR__ . '/404.php';
}
?>
