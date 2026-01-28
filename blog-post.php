<?php
/**
 * Страница статьи блога
 */
require_once __DIR__ . '/config/database.php';

$db = getDB();
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$pageTitle = htmlspecialchars($post['title']);
$metaDescription = htmlspecialchars($post['subtitle'] ?? '');

$image = htmlspecialchars($post['image_path']);
$title = htmlspecialchars($post['title']);
$subtitle = htmlspecialchars($post['subtitle']);
$content = $post['content']; // HTML из админки

$pageContent = <<<HTML
<nav class="container mx-auto max-w-7xl px-4 pt-6 text-sm" aria-label="Хлебные крошки">
  <div class="breadcrumbs">
    <ul>
      <li><a href="index.php">Главная</a></li>
      <li><a href="blog.php">Блог</a></li>
      <li class="font-semibold">{$title}</li>
    </ul>
  </div>
</nav>

<section class="container mx-auto max-w-7xl px-4 py-12 bw-section">
  <h1 class="text-4xl font-extrabold">{$title}</h1>
  <p class="mt-3 max-w-3xl">{$subtitle}</p>

  <div class="mt-8 border border-black bg-white p-4">
    <img src="{$image}" alt="{$title}" class="w-full h-auto object-cover" />
  </div>

  <article class="mt-10 max-w-4xl space-y-4">
    {$content}
  </article>
</section>
HTML;

include __DIR__ . '/templates/base.php';

