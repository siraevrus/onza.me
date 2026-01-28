<?php
/**
 * Универсальный роутер для обработки запросов к несуществующим файлам
 * Используется встроенным PHP сервером через router.php
 * 
 * Запуск: php -S localhost:8000 router.php
 */
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$filePath = __DIR__ . $path;

// Если запрашивается существующий файл или директория, отдаем его как есть
if (file_exists($filePath) && is_file($filePath)) {
    return false; // Пусть сервер обработает сам
}

// Если запрашивается директория, пробуем index.php
if (is_dir($filePath)) {
    $indexFile = $filePath . '/index.php';
    if (file_exists($indexFile)) {
        include $indexFile;
        return true;
    }
}

// Извлекаем имя файла из пути
$filename = basename($path);
$filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

// Пробуем найти PHP файл проекта
$projectPhpFile = __DIR__ . '/' . $filenameWithoutExt . '.php';
if (file_exists($projectPhpFile)) {
    include $projectPhpFile;
    return true;
}

// Пробуем найти HTML файл проекта
$projectHtmlFile = __DIR__ . '/' . $filenameWithoutExt . '.html';
if (file_exists($projectHtmlFile)) {
    include $projectHtmlFile;
    return true;
}

// Если файл не найден, проверяем БД на наличие проекта с таким именем
require_once __DIR__ . '/config/database.php';
$db = getDB();

// Ищем проект по ссылке (может быть с расширением или без)
// Проверяем разные варианты: с .php, без расширения, и просто имя файла
$projectLink = $filenameWithoutExt . '.php';
$stmt = $db->prepare("SELECT * FROM projects WHERE link = ? OR link = ? OR link LIKE ? OR link = ?");
$stmt->execute([$projectLink, $filenameWithoutExt, '%' . $filenameWithoutExt . '%', basename($path)]);
$project = $stmt->fetch();

if ($project) {
    // Найден проект в БД - показываем страницу проекта
    include __DIR__ . '/templates/project_page.php';
    return true;
}

// Если ничего не найдено, показываем 404
http_response_code(404);
include __DIR__ . '/404.php';
return true;
