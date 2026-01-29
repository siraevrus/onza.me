<?php
/**
 * Страница проекта: project-hr-saas
 * Загружает статический HTML файл или проект из БД
 */
require_once __DIR__ . '/../config/database.php';

$htmlFile = __DIR__ . '/../project-hr-saas.html';
if (file_exists($htmlFile)) {
    include $htmlFile;
} else {
    // Если HTML файл не существует, проверяем БД
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
        include __DIR__ . '/../templates/project_page.php';
    } else {
        http_response_code(404);
        include __DIR__ . '/../404.php';
    }
}