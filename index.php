<?php
/**
 * Главная страница - загружается из БД или использует статический контент
 */
require_once __DIR__ . '/config/database.php';

function getPageBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM pages WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

// Загружаем проекты из БД для отображения на главной
$db = getDB();
$projects = $db->query("SELECT * FROM projects ORDER BY display_order ASC, id DESC LIMIT 6")->fetchAll();
$blogPosts = $db->query("SELECT * FROM blog_posts ORDER BY display_order ASC, id DESC LIMIT 3")->fetchAll();

function renderBlogBlockHtml(array $posts): string {
    ob_start();
    if (empty($posts)) {
        echo '<div class="text-gray-500">Статей пока нет.</div>';
    } else {
        foreach ($posts as $p) {
            $img = htmlspecialchars($p['image_path']);
            $title = htmlspecialchars($p['title']);
            $subtitle = htmlspecialchars($p['subtitle']);
            $id = (int)($p['id'] ?? 0);
            echo <<<HTML
<a class="card card-zoom h-full" href="blog-post.php?id={$id}">
  <figure class="h-48 overflow-hidden">
    <img class="w-full h-full object-cover" src="{$img}" alt="{$title}" />
  </figure>
  <div class="card-body">
    <h3 class="card-title">{$title}</h3>
    <p class="mt-2">{$subtitle}</p>
    <div class="mt-4">
      <span class="link link-hover text-black">Читать ↗</span>
    </div>
  </div>
</a>
HTML;
        }
    }
    return (string)ob_get_clean();
}

$page = getPageBySlug('index');

if ($page) {
    // Загружаем страницу из БД
    $pageTitle = htmlspecialchars($page['title']);
    $metaDescription = htmlspecialchars($page['meta_description'] ?? '');
    $pageContent = $page['content'];

    // CTA теперь вставляется глобально перед футером в templates/base.php.
    // Если в контенте главной уже есть этот блок (из старых версий), удаляем,
    // чтобы не было дублей и чтобы CTA был строго над футером.
    if (stripos($pageContent, 'Готовы обсудить ваш проект?') !== false) {
        $ctaPattern = '/<section[^>]*>[\s\S]*?<h2[^>]*>\s*Готовы обсудить ваш проект\?\s*<\/h2>[\s\S]*?<\/section>/u';
        $pageContent = preg_replace($ctaPattern, '', $pageContent, 1);
    }
    
    // Заменяем статический блок проектов на динамический
    // Если в контенте есть маркер для проектов, заменяем его
    if (strpos($pageContent, '<!--PROJECTS_START-->') !== false && strpos($pageContent, '<!--PROJECTS_END-->') !== false) {
        ob_start();
        include __DIR__ . '/templates/projects_block.php';
        $projectsHtml = ob_get_clean();
        $pageContent = preg_replace('/<!--PROJECTS_START-->.*?<!--PROJECTS_END-->/s', $projectsHtml, $pageContent);
    }

    // Подставляем блок блога, если есть маркер
    if (strpos($pageContent, '<!--BLOG_BLOCK-->') !== false) {
        $pageContent = str_replace('<!--BLOG_BLOCK-->', trim(renderBlogBlockHtml($blogPosts)), $pageContent);
    }
    
    include __DIR__ . '/templates/base.php';
} else {
    // Используем статический контент из index.html, но заменяем блок проектов
    $indexContent = file_get_contents(__DIR__ . '/index.html');
    
    // Заменяем маркер PROJECTS_BLOCK на динамический контент
    if (strpos($indexContent, '<!--PROJECTS_BLOCK-->') !== false) {
        ob_start();
        include __DIR__ . '/templates/projects_block.php';
        $projectsHtml = ob_get_clean();
        $indexContent = str_replace('<!--PROJECTS_BLOCK-->', trim($projectsHtml), $indexContent);
    }

    if (strpos($indexContent, '<!--BLOG_BLOCK-->') !== false) {
        $indexContent = str_replace('<!--BLOG_BLOCK-->', trim(renderBlogBlockHtml($blogPosts)), $indexContent);
    }
    
    // Заменяем футер на динамический
    $GLOBALS['_footer_functions_only'] = true;
    require_once __DIR__ . '/templates/footer.php';
    $indexContent = replaceFooterInHtml($indexContent);
    
    echo $indexContent;
}
?>
