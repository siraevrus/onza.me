<?php
/**
 * Скрипт для импорта существующих HTML страниц в БД
 * Запустите один раз для переноса контента в CMS
 */
require_once __DIR__ . '/config/auth.php';
requireAuth();
require_once __DIR__ . '/config/database.php';

$db = getDB();

// Список страниц для импорта (slug => путь к файлу)
$pagesToImport = [
    'index' => 'index.html',
    'services' => 'services.html',
    'contacts' => 'contacts.html',
    'vacancies' => 'vacancies.html',
    'blog' => 'blog.html',
    'privacy' => 'privacy.html',
    'service-analytics' => 'service-analytics.html',
    'service-design' => 'service-design.html',
    'service-backend' => 'service-backend.html',
    'service-mobile' => 'service-mobile.html',
    'service-qa' => 'service-qa.html',
    'service-support' => 'service-support.html',
];

$imported = 0;
$errors = [];

foreach ($pagesToImport as $slug => $filePath) {
    $fullPath = __DIR__ . '/' . $filePath;
    
    if (!file_exists($fullPath)) {
        $errors[] = "Файл не найден: $filePath";
        continue;
    }
    
    $content = file_get_contents($fullPath);
    
    // Извлекаем title из HTML
    preg_match('/<title>(.*?)<\/title>/i', $content, $titleMatch);
    $title = $titleMatch[1] ?? ucfirst($slug);
    
    // Извлекаем meta description
    preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/i', $content, $descMatch);
    $metaDescription = $descMatch[1] ?? '';
    
    // Извлекаем содержимое между <main> и </main>
    if (preg_match('/<main[^>]*class=["\']flex-1["\'][^>]*>(.*?)<\/main>/is', $content, $mainMatch)) {
        $pageContent = trim($mainMatch[1]);
    } elseif (preg_match('/<main[^>]*>(.*?)<\/main>/is', $content, $mainMatch)) {
        $pageContent = trim($mainMatch[1]);
    } elseif (preg_match('/<body[^>]*>(.*?)<\/body>/is', $content, $bodyMatch)) {
        // Убираем header и footer если есть
        $bodyContent = $bodyMatch[1];
        $bodyContent = preg_replace('/<header[^>]*>.*?<\/header>/is', '', $bodyContent);
        $bodyContent = preg_replace('/<footer[^>]*>.*?<\/footer>/is', '', $bodyContent);
        $bodyContent = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $bodyContent);
        $pageContent = trim($bodyContent);
    } else {
        $pageContent = $content;
    }
    
    // Очищаем от лишних пробелов и переносов строк
    $pageContent = preg_replace('/\s+/', ' ', $pageContent);
    $pageContent = trim($pageContent);
    
    // Проверяем, существует ли уже страница
    $stmt = $db->prepare("SELECT COUNT(*) FROM pages WHERE slug = ?");
    $stmt->execute([$slug]);
    
    if ($stmt->fetchColumn() > 0) {
        // Обновляем существующую страницу
        $stmt = $db->prepare("UPDATE pages SET title = ?, meta_description = ?, content = ?, updated_at = CURRENT_TIMESTAMP WHERE slug = ?");
        $stmt->execute([$title, $metaDescription, $pageContent, $slug]);
        $imported++;
    } else {
        // Создаем новую страницу
        $stmt = $db->prepare("INSERT INTO pages (slug, title, meta_description, content) VALUES (?, ?, ?, ?)");
        $stmt->execute([$slug, $title, $metaDescription, $pageContent]);
        $imported++;
    }
}

echo "<!doctype html><html><head><meta charset='utf-8'><title>Импорт страниц</title></head><body>";
echo "<h1>Импорт завершен</h1>";
echo "<p>Импортировано страниц: $imported</p>";

if (!empty($errors)) {
    echo "<h2>Ошибки:</h2><ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

echo "<p><a href='admin.php?tab=pages'>Перейти в админ-панель</a></p>";
echo "</body></html>";
?>
