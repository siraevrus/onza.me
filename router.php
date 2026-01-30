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

// Если запрашивается существующий файл (не PHP), отдаем его напрямую
// Это важно для CSS, JS, изображений и других статических файлов
if (file_exists($filePath) && is_file($filePath) && !preg_match('/\.php$/', $path)) {
    return false; // Пусть встроенный сервер обработает сам
}

// Обработка путей вида /projects/opharme.php или /services/service-analytics.php
// Проверяем ПЕРЕД проверкой директории, чтобы файлы в папках имели приоритет
if (preg_match('#^/(projects|services)/([^/]+\.php)$#', $path, $matches)) {
    $folder = $matches[1];
    $filename = $matches[2];
    $fullPath = __DIR__ . '/' . $folder . '/' . $filename;
    if (file_exists($fullPath)) {
        include $fullPath;
        return true;
    }
    
    // Если файл не существует и это услуга, проверяем БД
    if ($folder === 'services') {
        $slug = pathinfo($filename, PATHINFO_FILENAME);
        require_once __DIR__ . '/config/database.php';
        $db = getDB();
        
        try {
            $stmt = $db->prepare("SELECT * FROM services WHERE slug = ? AND is_active = 1");
            $stmt->execute([$slug]);
            $service = $stmt->fetch();
            
            if ($service) {
                // Загружаем блоки услуги
                try {
                    $blocksStmt = $db->prepare("SELECT * FROM service_blocks WHERE service_id = ? ORDER BY display_order ASC, id ASC");
                    $blocksStmt->execute([$service['id']]);
                    $blocks = $blocksStmt->fetchAll();
                } catch (Exception $e) {
                    $blocks = [];
                }
                
                if (!isset($blocks)) {
                    $blocks = [];
                }
                
                include __DIR__ . '/templates/service_page.php';
                return true;
            }
        } catch (Exception $e) {
            // Игнорируем ошибки БД, продолжаем поиск
        }
    }
}

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

// Пробуем найти PHP файл проекта в папке projects/
$projectPhpFile = __DIR__ . '/projects/' . $filenameWithoutExt . '.php';
if (file_exists($projectPhpFile)) {
    include $projectPhpFile;
    return true;
}

// Пробуем найти HTML файл проекта в папке projects/
$projectHtmlFile = __DIR__ . '/projects/' . $filenameWithoutExt . '.html';
if (file_exists($projectHtmlFile)) {
    include $projectHtmlFile;
    return true;
}

// Пробуем найти PHP файл проекта в корне (для обратной совместимости)
$projectPhpFileRoot = __DIR__ . '/' . $filenameWithoutExt . '.php';
if (file_exists($projectPhpFileRoot)) {
    include $projectPhpFileRoot;
    return true;
}

// Пробуем найти HTML файл проекта в корне (для обратной совместимости)
$projectHtmlFileRoot = __DIR__ . '/' . $filenameWithoutExt . '.html';
if (file_exists($projectHtmlFileRoot)) {
    include $projectHtmlFileRoot;
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
