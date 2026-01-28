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
<a class="block py-4 card-zoom bg-white" href="blog-post.php?id={$id}">
  <div class="flex items-start gap-6">
    <img class="h-[12rem] w-[18rem] object-cover" src="{$img}" alt="{$title}" />
    <div class="flex-1 flex flex-col h-[12rem]">
      <div>
        <h3 class="text-xl font-semibold">{$title}</h3>
        <p class="mt-2">{$subtitle}</p>
      </div>
      <div class="mt-auto pt-3 font-semibold">Читать ↗</div>
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

    // Переносим CTA "Готовы обсудить ваш проект?" сразу под hero-заголовок,
    // если страница хранится в БД и блоки стоят иначе.
    if (stripos($pageContent, 'Готовы обсудить ваш проект?') !== false && stripos($pageContent, 'Превращаем технологии') !== false) {
        $ctaPattern = '/<section[^>]*>[\s\S]*?<h2[^>]*>\s*Готовы обсудить ваш проект\?\s*<\/h2>[\s\S]*?<\/section>/u';
        if (preg_match($ctaPattern, $pageContent, $m)) {
            $ctaBlock = $m[0];

            // Обновляем цвет подложки CTA только ВНУТРИ рамки (если он размечен как bg-white)
            $ctaBlock = str_replace(' w-full bg-white ', ' w-full bg-[#EDF95A] ', $ctaBlock);
            $ctaBlock = str_replace(' w-full bg-white"', ' w-full bg-[#EDF95A]"', $ctaBlock);
            // Убираем обводку у CTA (если она есть)
            $ctaBlock = str_replace(' border border-black', '', $ctaBlock);
            // Скругление контейнера CTA (внутри блока)
            $ctaBlock = str_replace('relative w-full ', 'relative w-full rounded-2xl ', $ctaBlock);
            // Убираем обводку у кнопки в CTA
            $ctaBlock = str_replace('class="btn btn-arrow"', 'class="btn btn-arrow btn-noborder"', $ctaBlock);
            $pageContent = preg_replace($ctaPattern, '', $pageContent, 1);

            $heroPos = stripos($pageContent, 'Превращаем технологии');
            if ($heroPos !== false) {
                $closePos = strpos($pageContent, '</section>', $heroPos);
                if ($closePos !== false) {
                    $insertPos = $closePos + strlen('</section>');
                    $pageContent = substr($pageContent, 0, $insertPos) . "\n\n" . $ctaBlock . "\n\n" . substr($pageContent, $insertPos);
                }
            }
        }
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
    
    echo $indexContent;
}
?>
