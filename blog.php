<?php
/**
 * Страница: blog
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

function renderBlogListHtml(array $posts): string {
    ob_start();
    ?>
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php if (empty($posts)): ?>
            <div class="col-span-full text-gray-500">Статей пока нет.</div>
        <?php else: ?>
            <?php foreach ($posts as $p): ?>
                <a class="block card-zoom bg-white" href="blog-post.php?id=<?php echo (int)$p['id']; ?>">
                    <figure class="h-96 overflow-hidden">
                        <img class="w-full h-full object-cover"
                             src="<?php echo htmlspecialchars($p['image_path']); ?>"
                             alt="<?php echo htmlspecialchars($p['title']); ?>" />
                    </figure>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo htmlspecialchars($p['title']); ?></h3>
                        <p class="mt-2"><?php echo htmlspecialchars($p['subtitle']); ?></p>
                        <div class="mt-4 font-semibold">Читать ↗</div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
    return (string)ob_get_clean();
}

$BLOG_PER_PAGE = 8;

function buildPageUrl(int $page): string {
    $basePath = parse_url($_SERVER['REQUEST_URI'] ?? '/blog.php', PHP_URL_PATH) ?: '/blog.php';
    $params = $_GET ?? [];
    $params['page'] = $page;
    return $basePath . '?' . http_build_query($params);
}

function renderBlogPaginationHtml(int $currentPage, int $totalPages): string {
    if ($totalPages <= 1) return '';

    $currentPage = max(1, min($totalPages, $currentPage));

    $items = [];
    // Всегда показываем 1 и последнюю
    $items[] = 1;
    $items[] = $totalPages;

    // Окно вокруг текущей
    for ($p = $currentPage - 2; $p <= $currentPage + 2; $p++) {
        if ($p >= 1 && $p <= $totalPages) $items[] = $p;
    }

    $items = array_values(array_unique($items));
    sort($items);

    ob_start();
    ?>
    <nav class="mt-10 flex justify-center" aria-label="Пагинация">
        <div class="join">
            <a class="btn btn-ghost join-item <?php echo $currentPage <= 1 ? 'btn-disabled' : ''; ?>"
               href="<?php echo htmlspecialchars(buildPageUrl(max(1, $currentPage - 1))); ?>">
                ←
            </a>

            <?php
            $prev = null;
            foreach ($items as $p):
                if ($prev !== null && $p > $prev + 1): ?>
                    <button class="btn btn-ghost join-item btn-disabled">…</button>
                <?php endif; ?>

                <a class="btn btn-ghost join-item <?php echo $p === $currentPage ? 'btn-active' : ''; ?>"
                   href="<?php echo htmlspecialchars(buildPageUrl($p)); ?>">
                    <?php echo (int)$p; ?>
                </a>
            <?php
                $prev = $p;
            endforeach; ?>

            <a class="btn btn-ghost join-item <?php echo $currentPage >= $totalPages ? 'btn-disabled' : ''; ?>"
               href="<?php echo htmlspecialchars(buildPageUrl(min($totalPages, $currentPage + 1))); ?>">
                →
            </a>
        </div>
    </nav>
    <?php
    return (string)ob_get_clean();
}

$slug = 'blog';
$page = getPageBySlug($slug);

$pageNum = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = $BLOG_PER_PAGE;
$offset = ($pageNum - 1) * $perPage;

$totalPosts = (int)$db->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
$totalPages = max(1, (int)ceil($totalPosts / $perPage));
if ($pageNum > $totalPages) {
    $pageNum = $totalPages;
    $offset = ($pageNum - 1) * $perPage;
}

$stmt = $db->prepare("SELECT * FROM blog_posts ORDER BY display_order ASC, id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

$blogListHtml = renderBlogListHtml($posts) . renderBlogPaginationHtml($pageNum, $totalPages);

if ($page) {
    $pageTitle = htmlspecialchars($page['title']);
    $metaDescription = htmlspecialchars($page['meta_description'] ?? '');
    $pageContent = $page['content'];

    if (strpos($pageContent, '<!--BLOG_LIST-->') !== false) {
        $pageContent = str_replace('<!--BLOG_LIST-->', $blogListHtml, $pageContent);
    } else {
        $pageContent .= "\n" . $blogListHtml;
    }
    include __DIR__ . '/templates/base.php';
} else {
    // Если страница не найдена в БД, используем статический шаблон + подставляем список
    $staticFile = __DIR__ . '/blog.html';
    if (!file_exists($staticFile)) {
        http_response_code(404);
        echo '<!doctype html><html><head><title>404 - Страница не найдена</title></head><body><h1>404 - Страница не найдена</h1><p><a href="index.php">Вернуться на главную</a></p></body></html>';
        exit;
    }

    $html = file_get_contents($staticFile);
    if (strpos($html, '<!--BLOG_LIST-->') !== false) {
        $html = str_replace('<!--BLOG_LIST-->', $blogListHtml, $html);
    }
    echo $html;
}