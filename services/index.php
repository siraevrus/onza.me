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

$db = getDB();

function renderServicesListHtml(array $services): string {
    ob_start();
    if (empty($services)): ?>
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500">Услуг пока нет.</p>
        </div>
    <?php else: ?>
        <?php foreach ($services as $service): ?>
            <div class="card card-zoom">
                <div class="card-body">
                    <h2 class="card-title"><?php echo htmlspecialchars($service['title']); ?></h2>
                    <p><?php echo htmlspecialchars($service['subtitle']); ?></p>
                    <div class="mt-3">
                        <a href="/services/<?php echo htmlspecialchars($service['slug']); ?>.php" class="link link-hover">Подробнее ↗</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif;
    return (string)ob_get_clean();
}

$slug = 'services';
$page = getPageBySlug($slug);

// Загружаем услуги из БД
try {
    $services = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY display_order ASC, id DESC")->fetchAll();
} catch (Exception $e) {
    // Если таблицы еще нет, создаем пустой массив
    $services = [];
}
$servicesHtml = renderServicesListHtml($services);

if ($page) {
    $pageTitle = htmlspecialchars($page['title']);
    $metaDescription = htmlspecialchars($page['meta_description'] ?? '');
    
    $breadcrumbs = '<nav class="container mx-auto max-w-7xl px-4 pt-6 text-sm" aria-label="Хлебные крошки">
        <div class="breadcrumbs">
            <ul>
                <li><a href="/index.php">Главная</a></li>
                <li class="font-semibold">Услуги</li>
            </ul>
        </div>
    </nav>';
    
    $pageContent = $page['content'];
    
    // Если в контенте есть плейсхолдер, заменяем
    if (strpos($pageContent, '<!--SERVICES_LIST-->') !== false) {
        $pageContent = str_replace('<!--SERVICES_LIST-->', $servicesHtml, $pageContent);
    } else {
        // Ищем секцию с grid в контенте и заменяем её содержимое
        // Паттерн: ищем <div class="... grid ..."> или <div class="...grid...">
        if (preg_match('/<div[^>]*class="[^"]*\bgrid\b[^"]*"[^>]*>/', $pageContent)) {
            // Заменяем содержимое grid на список услуг из БД
            // Используем более точный паттерн: от открывающего div grid до закрывающего div перед </section>
            $pageContent = preg_replace('/(<div[^>]*class="[^"]*\bgrid\b[^"]*"[^>]*>)[\s\S]*?(<\/div>\s*<\/section>)/', '$1' . "\n                " . $servicesHtml . "\n            " . '$2', $pageContent, 1);
        } else {
            // Если секции нет, добавляем список услуг после контента страницы
            $pageContent .= '<section class="container mx-auto max-w-7xl px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                ' . $servicesHtml . '
            </div>
        </section>';
        }
    }
    
    // Добавляем breadcrumbs в начало
    $pageContent = $breadcrumbs . $pageContent;
    
    include __DIR__ . '/../templates/base.php';
} else {
    // Если страница не найдена в БД, пробуем загрузить статический файл
    $staticFile = __DIR__ . '/../services.html';
    if (file_exists($staticFile)) {
        $html = file_get_contents($staticFile);
        
        // Добавляем breadcrumbs если их нет
        if (strpos($html, 'breadcrumbs') === false) {
            $breadcrumbs = '<nav class="container mx-auto max-w-7xl px-4 pt-6 text-sm" aria-label="Хлебные крошки">
        <div class="breadcrumbs">
            <ul>
                <li><a href="/index.php">Главная</a></li>
                <li class="font-semibold">Услуги</li>
            </ul>
        </div>
    </nav>';
            $html = preg_replace('/(<main[^>]*>)/', '$1' . $breadcrumbs, $html, 1);
        }
        
        // Добавляем список услуг
        if (strpos($html, '<!--SERVICES_LIST-->') !== false) {
            $html = str_replace('<!--SERVICES_LIST-->', $servicesHtml, $html);
        } else {
            // Ищем секцию с grid - класс grid может быть вместе с другими классами
            // Паттерн: <div class="... grid ..."> или <div class="...grid...">
            if (preg_match('/<div[^>]*class="[^"]*\bgrid\b[^"]*"[^>]*>/', $html)) {
                // Заменяем содержимое grid на список услуг из БД
                // Находим div с классом grid и заменяем всё содержимое до закрывающего </div> перед </section>
                $html = preg_replace('/(<div[^>]*class="[^"]*\bgrid\b[^"]*"[^>]*>)[\s\S]*?(<\/div>\s*<\/section>)/', '$1' . "\n                " . $servicesHtml . "\n            " . '$2', $html, 1);
            } else {
                // Если секции нет, добавляем после первой секции с заголовком
                $servicesSection = '<section class="container mx-auto max-w-7xl px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                ' . $servicesHtml . '
            </div>
        </section>';
                // Ищем закрывающий тег первой секции и добавляем после неё
                $html = preg_replace('/(<\/section>\s*)(<section[^>]*class="[^"]*bw-section[^"]*"[^>]*>)/s', '$1' . $servicesSection . "\n      " . '$2', $html, 1);
            }
        }
        
        echo $html;
    } else {
        // Если статического файла нет, создаем базовую страницу с услугами
        $pageTitle = 'Услуги';
        $breadcrumbs = '<nav class="container mx-auto max-w-7xl px-4 pt-6 text-sm" aria-label="Хлебные крошки">
        <div class="breadcrumbs">
            <ul>
                <li><a href="/index.php">Главная</a></li>
                <li class="font-semibold">Услуги</li>
            </ul>
        </div>
    </nav>';
        
        $pageContent = $breadcrumbs . '
      <section class="container mx-auto max-w-7xl px-4 py-12 bw-section">
        <h1 class="text-4xl font-extrabold">Услуги</h1>
      </section>
      
      <section class="container mx-auto max-w-7xl px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            ' . $servicesHtml . '
        </div>
      </section>';
        
        include __DIR__ . '/../templates/base.php';
    }
}