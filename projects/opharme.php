<?php
/**
 * Универсальный обработчик проекта
 * Загружает проект из БД
 */
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$filenameWithoutExt = basename(__FILE__, '.php');
// Ищем проект по разным вариантам ссылки
$stmt = $db->prepare("SELECT * FROM projects WHERE link = ? OR link = ? OR link = ? OR link LIKE ?");
$stmt->execute([
    $filenameWithoutExt . '.php',  // opharme.php
    $filenameWithoutExt,            // opharme
    basename(__FILE__),             // opharme.php (полное имя файла)
    '%' . $filenameWithoutExt . '%' // любой вариант с opharme
]);
$project = $stmt->fetch();

if ($project) {
    include __DIR__ . '/../templates/project_page.php';
} else {
    http_response_code(404);
    include __DIR__ . '/../404.php';
}
?>
