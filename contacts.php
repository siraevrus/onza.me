<?php
/**
 * Страница: contacts
 * Контент загружается из базы данных через CMS
 */
require_once __DIR__ . '/config/database.php';

function getPageBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM pages WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

$slug = 'contacts';
$page = getPageBySlug($slug);

if ($page) {
    $pageTitle = htmlspecialchars($page['title']);
    $metaDescription = htmlspecialchars($page['meta_description'] ?? '');
    $breadcrumbs = '<nav class="container mx-auto max-w-7xl px-4 pt-6 text-sm" aria-label="Хлебные крошки">
        <div class="breadcrumbs">
            <ul>
                <li><a href="/index.php">Главная</a></li>
                <li class="font-semibold">Контакты</li>
            </ul>
        </div>
    </nav>';
    $pageContent = $breadcrumbs . $page['content'];
    include __DIR__ . '/templates/base.php';
} else {
    // Если страница не найдена в БД, пробуем загрузить статический файл
    $staticFile = __DIR__ . '/contacts.html';
    if (file_exists($staticFile)) {
        $html = file_get_contents($staticFile);
        // Breadcrumbs уже должны быть в HTML файле, но на всякий случай проверим
        if (strpos($html, 'breadcrumbs') === false) {
            $breadcrumbs = '<nav class="container mx-auto max-w-7xl px-4 pt-6 text-sm" aria-label="Хлебные крошки">
        <div class="breadcrumbs">
            <ul>
                <li><a href="/index.php">Главная</a></li>
                <li class="font-semibold">Контакты</li>
            </ul>
        </div>
    </nav>';
            $html = preg_replace('/(<main[^>]*>)/', '$1' . $breadcrumbs, $html, 1);
        }

        // Заменяем футер на динамический
        $GLOBALS['_footer_functions_only'] = true;
        require_once __DIR__ . '/templates/footer.php';
        $html = replaceFooterInHtml($html);

        // Добавляем общий CTA "Готовы обсудить ваш проект?" над футером (если его ещё нет)
        if (strpos($html, 'data-cta-wave') === false && stripos($html, 'Готовы обсудить ваш проект?') === false) {
            ob_start();
            include __DIR__ . '/templates/cta.php';
            $ctaHtml = (string)ob_get_clean();
            if (stripos($html, '<footer') !== false) {
                $html = preg_replace('/<footer\b/i', $ctaHtml . "\n<footer", $html, 1);
            } elseif (stripos($html, '</body>') !== false) {
                $html = preg_replace('/<\/body>/i', $ctaHtml . "\n</body>", $html, 1);
            } else {
                $html .= "\n" . $ctaHtml;
            }
        }
        echo $html;
    } else {
        http_response_code(404);
        echo '<!doctype html><html><head><title>404 - Страница не найдена</title></head><body><h1>404 - Страница не найдена</h1><p><a href="/index.php">Вернуться на главную</a></p></body></html>';
    }
}