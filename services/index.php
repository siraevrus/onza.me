<?php
/**
 * Страница: services
 * Контент загружается из базы данных через CMS
 */
require_once __DIR__ . '/../config/database.php';

function getPageBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM pages WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

$slug = 'services';
$page = getPageBySlug($slug);

if ($page) {
    $pageTitle = htmlspecialchars($page['title']);
    $metaDescription = htmlspecialchars($page['meta_description'] ?? '');
    $pageContent = $page['content'];
    // Добавляем breadcrumbs только если их еще нет в контенте
    if (strpos($pageContent, 'breadcrumbs') === false) {
        $breadcrumbs = '<nav class="container mx-auto max-w-7xl px-4 pt-6 text-sm" aria-label="Хлебные крошки">
        <div class="breadcrumbs">
            <ul>
                <li><a href="/index.php">Главная</a></li>
                <li class="font-semibold">Услуги</li>
            </ul>
        </div>
    </nav>';
        $pageContent = $breadcrumbs . $pageContent;
    }
    include __DIR__ . '/../templates/base.php';
} else {
    // Если страница не найдена в БД, пробуем загрузить статический файл
    // Breadcrumbs уже есть в HTML файле, просто выводим его
    $staticFile = __DIR__ . '/../services.html';
    if (file_exists($staticFile)) {
        include $staticFile;
    } else {
        http_response_code(404);
        echo '<!doctype html><html><head><title>404 - Страница не найдена</title></head><body><h1>404 - Страница не найдена</h1><p><a href="/index.php">Вернуться на главную</a></p></body></html>';
    }
}