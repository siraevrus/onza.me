<?php
/**
 * Страница: vacancies
 * Контент загружается из базы данных через CMS
 */
require_once __DIR__ . '/config/database.php';

function getPageBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM pages WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

$db = getDB();

function renderVacanciesListHtml(array $vacancies): string {
    ob_start();
    if (empty($vacancies)): ?>
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500">Вакансий пока нет.</p>
        </div>
    <?php else: ?>
        <?php foreach ($vacancies as $v): ?>
            <div class="card card-zoom">
                <div class="card-body">
                    <h3 class="card-title"><?php echo htmlspecialchars($v['title']); ?></h3>
                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($v['subtitle'])); ?></p>
                    <div class="mt-4"><a href="contacts.php#contact-form" class="btn btn-arrow">Связаться</a></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif;
    return (string)ob_get_clean();
}

$slug = 'vacancies';
$page = getPageBySlug($slug);

if ($page) {
    $pageTitle = htmlspecialchars($page['title']);
    $metaDescription = htmlspecialchars($page['meta_description'] ?? '');
    // Подмешиваем список вакансий из БД.
    $vacancies = $db->query("SELECT * FROM vacancies ORDER BY display_order ASC, id DESC")->fetchAll();
    $vacanciesHtml = renderVacanciesListHtml($vacancies);

    $breadcrumbs = '<nav class="container mx-auto max-w-7xl px-4 pt-6 text-sm" aria-label="Хлебные крошки">
        <div class="breadcrumbs">
            <ul>
                <li><a href="/index.php">Главная</a></li>
                <li class="font-semibold">Вакансии</li>
            </ul>
        </div>
    </nav>';
    $pageContent = $breadcrumbs . $page['content'];
    // Если в контенте есть плейсхолдер, заменяем, иначе добавляем в конец.
    if (strpos($pageContent, '<!--VACANCIES_LIST-->') !== false) {
        $pageContent = str_replace('<!--VACANCIES_LIST-->', $vacanciesHtml, $pageContent);
    } else {
        $pageContent .= "\n" . $vacanciesHtml;
    }
    include __DIR__ . '/templates/base.php';
} else {
    // Если страница не найдена в БД, используем статический шаблон + подставляем список вакансий
    $staticFile = __DIR__ . '/vacancies.html';
    if (file_exists($staticFile)) {
        $vacancies = $db->query("SELECT * FROM vacancies ORDER BY display_order ASC, id DESC")->fetchAll();
        $vacanciesHtml = renderVacanciesListHtml($vacancies);

        $html = file_get_contents($staticFile);
        // Breadcrumbs уже должны быть в HTML файле, но на всякий случай проверим
        if (strpos($html, 'breadcrumbs') === false) {
            $breadcrumbs = '<nav class="container mx-auto max-w-7xl px-4 pt-6 text-sm" aria-label="Хлебные крошки">
        <div class="breadcrumbs">
            <ul>
                <li><a href="/index.php">Главная</a></li>
                <li class="font-semibold">Вакансии</li>
            </ul>
        </div>
    </nav>';
            $html = preg_replace('/(<main[^>]*>)/', '$1' . $breadcrumbs, $html, 1);
        }
        if (strpos($html, '<!--VACANCIES_LIST-->') !== false) {
            $html = str_replace('<!--VACANCIES_LIST-->', $vacanciesHtml, $html);
        } else {
            // если плейсхолдера нет, просто добавим в конец <main>
            $html = preg_replace('/<\/main>/i', $vacanciesHtml . "\n</main>", $html, 1);
        }
        echo $html;
    } else {
        http_response_code(404);
        echo '<!doctype html><html><head><title>404 - Страница не найдена</title></head><body><h1>404 - Страница не найдена</h1><p><a href="index.php">Вернуться на главную</a></p></body></html>';
    }
}