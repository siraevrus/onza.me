<?php
/**
 * Страница: privacy
 * Контент загружается из базы данных через CMS
 */
require_once __DIR__ . '/config/database.php';

function getPageBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM pages WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

$slug = 'privacy';
$page = getPageBySlug($slug);

if ($page) {
    $pageTitle = htmlspecialchars($page['title']);
    $metaDescription = htmlspecialchars($page['meta_description'] ?? '');
    $breadcrumbs = '<nav class="container mx-auto max-w-7xl px-4 pt-6 text-sm" aria-label="Хлебные крошки">
        <div class="breadcrumbs">
            <ul>
                <li><a href="/index.php">Главная</a></li>
                <li class="font-semibold">Политика конфиденциальности</li>
            </ul>
        </div>
    </nav>';
    
    // Обертываем контент в контейнер, как в privacy.html
    $pageContent = $breadcrumbs . '
      <section class="container mx-auto max-w-7xl px-4 py-12 bw-section">
        <h1 class="text-4xl font-extrabold">' . htmlspecialchars($page['title']) . '</h1>
      </section>

      <section class="bw-section">
        <div class="container mx-auto max-w-7xl px-4 py-12">
          <div class="prose prose-lg max-w-none">
            ' . $page['content'] . '
          </div>
        </div>
      </section>';
    include __DIR__ . '/templates/base.php';
} else {
    // Если страница не найдена в БД, пробуем загрузить статический файл
    $staticFile = __DIR__ . '/privacy.html';
    if (file_exists($staticFile)) {
        $html = file_get_contents($staticFile);
        // Breadcrumbs уже должны быть в HTML файле, но на всякий случай проверим
        if (strpos($html, 'breadcrumbs') === false) {
            $breadcrumbs = '<nav class="container mx-auto max-w-7xl px-4 pt-6 text-sm" aria-label="Хлебные крошки">
        <div class="breadcrumbs">
            <ul>
                <li><a href="/index.php">Главная</a></li>
                <li class="font-semibold">Политика конфиденциальности</li>
            </ul>
        </div>
    </nav>';
            $html = preg_replace('/(<main[^>]*>)/', '$1' . $breadcrumbs, $html, 1);
        }
        echo $html;
    } else {
        http_response_code(404);
        echo '<!doctype html><html><head><title>404 - Страница не найдена</title></head><body><h1>404 - Страница не найдена</h1><p><a href="/index.php">Вернуться на главную</a></p></body></html>';
    }
}