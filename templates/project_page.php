<!doctype html>
<html lang="ru" data-theme="corporate">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo htmlspecialchars($project['title']); ?> - ONZA.me</title>
    <meta name="description" content="<?php echo htmlspecialchars($project['description']); ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geologica:wght,CRSV@100..900,0&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
    <link href="/assets/styles.css" rel="stylesheet" />
    <script src="/assets/header.js" defer></script>
    
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m,e,t,r,i,k,a){
            m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
        })(window, document,'script','https://mc.yandex.ru/metrika/tag.js', 'ym');

        ym(93851165, 'init', {clickmap:true, referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/93851165" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
</head>
<body class="min-h-screen flex flex-col bg-grid">
    <header></header>

    <main class="flex-1">
        <nav class="container mx-auto max-w-7xl px-4 pt-6 text-sm" aria-label="Хлебные крошки">
            <div class="breadcrumbs">
                <ul>
                    <li><a href="/index.php">Главная</a></li>
                    <li><a href="../projects">Проекты</a></li>
                    <li class="font-semibold"><?php echo htmlspecialchars($project['title']); ?></li>
                </ul>
            </div>
        </nav>

        <section class="container mx-auto max-w-7xl px-4 py-12 bw-section">
            <h1 class="text-4xl font-extrabold"><?php echo htmlspecialchars($project['title']); ?></h1>
            <?php if (!empty($project['website'])): 
                $website = trim($project['website']);
                $websiteUrl = preg_match('/^https?:\/\//', $website) ? $website : 'https://' . $website;
            ?>
            <div class="mt-3">
                <a href="<?php echo htmlspecialchars($websiteUrl); ?>" target="_blank" rel="noopener noreferrer" class="link link-hover text-lg font-semibold">
                    <?php echo htmlspecialchars($website); ?> ↗
                </a>
            </div>
            <?php endif; ?>
            <div class="mt-3 flex items-center gap-3 flex-wrap text-xs">
                <?php 
                $tags = explode(',', $project['tags']);
                foreach ($tags as $tag): 
                    $tag = trim($tag);
                    if (!empty($tag)):
                        // Проверяем, является ли тег URL
                        $isUrl = preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/', $tag);
                        if ($isUrl):
                            // Добавляем https:// если отсутствует
                            $url = preg_match('/^https?:\/\//', $tag) ? $tag : 'https://' . $tag;
                ?>
                    <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener noreferrer" class="badge badge-outline hover:badge-primary"><?php echo htmlspecialchars($tag); ?></a>
                <?php else: ?>
                    <span class="badge"><?php echo htmlspecialchars($tag); ?></span>
                <?php 
                        endif;
                    endif;
                endforeach; 
                ?>
            </div>
            <div class="mt-3 max-w-3xl prose prose-lg max-w-none">
                <?php echo $project['description']; ?>
            </div>

            <?php
            $whatDoneRaw = trim((string)($project['what_done'] ?? ''));
            $techRaw = trim((string)($project['technologies'] ?? ''));

            $toBullets = function (string $text): array {
                $text = str_replace(["\r\n", "\r"], "\n", $text);
                $lines = array_map('trim', explode("\n", $text));
                $items = [];
                foreach ($lines as $line) {
                    if ($line === '') continue;
                    // убираем возможные маркеры "- " или "• "
                    $line = preg_replace('/^[-•\s]+/u', '', $line);
                    if ($line === '') continue;
                    $items[] = $line;
                }
                return $items;
            };

            $whatDoneItems = $whatDoneRaw !== '' ? $toBullets($whatDoneRaw) : [];
            $techItems = $techRaw !== '' ? $toBullets($techRaw) : [];
            ?>

            <?php if (!empty($whatDoneItems) || !empty($techItems)): ?>
                <div class="mt-8 grid gap-10 md:grid-cols-2">
                    <?php if (!empty($whatDoneItems)): ?>
                        <div>
                            <div class="text-xl font-bold">Что сделали</div>
                            <ul class="mt-3 list-disc pl-5 space-y-2">
                                <?php foreach ($whatDoneItems as $item): ?>
                                    <li><?php echo htmlspecialchars($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($techItems)): ?>
                        <div>
                            <div class="text-xl font-bold">Технологии</div>
                            <ul class="mt-3 list-disc pl-5 space-y-2">
                                <?php foreach ($techItems as $item): ?>
                                    <li><?php echo htmlspecialchars($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php
            // Загружаем изображения галереи для проекта
            require_once __DIR__ . '/../config/database.php';
            $db = getDB();
            $galleryStmt = $db->prepare("SELECT * FROM project_gallery WHERE project_id = ? ORDER BY display_order ASC, id ASC");
            $galleryStmt->execute([$project['id']]);
            $galleryImages = $galleryStmt->fetchAll();
            
            // Функция для нормализации пути к изображению (добавляет / в начало, если его нет)
            function normalizeImagePath($path) {
                $path = trim($path);
                if (empty($path)) return '';
                // Если путь уже абсолютный (начинается с /), возвращаем как есть
                if (strpos($path, '/') === 0) {
                    return $path;
                }
                // Если путь относительный, добавляем / в начало
                return '/' . $path;
            }
            
            if (!empty($galleryImages)):
            ?>
            <!-- Gallery (с нуля) -->
            <div class="mt-8">
                <div class="relative w-full bg-white">
                    <button type="button" class="gallery-prev absolute left-2 top-1/2 -translate-y-1/2 z-10 btn btn-ghost btn-sm" aria-label="Предыдущее">‹</button>
                    <img id="gallery-main" class="w-full h-auto block cursor-zoom-in bg-white transition-opacity duration-300" src="<?php echo htmlspecialchars(normalizeImagePath($galleryImages[0]['image_path'])); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" />
                    <button type="button" class="gallery-next absolute right-2 top-1/2 -translate-y-1/2 z-10 btn btn-ghost btn-sm" aria-label="Следующее">›</button>
                </div>

                <div id="gallery-thumbs" class="mt-3 grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2">
                    <?php foreach ($galleryImages as $idx => $img): ?>
                        <button type="button"
                                class="gallery-thumb bg-white hover:opacity-80 relative <?php echo $idx === 0 ? 'gallery-thumb-active' : ''; ?>"
                                data-gallery-index="<?php echo (int)$idx; ?>"
                                aria-label="Открыть изображение <?php echo (int)($idx + 1); ?>">
                            <img src="<?php echo htmlspecialchars(normalizeImagePath($img['image_path'])); ?>" alt="" class="w-full h-16 object-cover" />
                            <div class="gallery-thumb-overlay absolute inset-0 bg-black opacity-30 transition-opacity duration-200"></div>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include __DIR__ . '/cta.php'; ?>

    <footer class="bg-white">
        <div class="container mx-auto max-w-7xl px-4 py-10 grid gap-6 md:grid-cols-4 items-start">
            <div>
                <img src="/assets/image/logo.svg" alt="ONZA.ME" class="logo-img mb-2" />
                <div class="mt-2">© Onza.me</div>
                <div class="mt-1">Все права защищены</div>
            </div>
            <div>
                <div class="font-semibold">Услуги</div>
                <ul class="mt-2 space-y-1">
                    <li><a class="link link-hover" href="../services/service-mobile.php">Мобильные приложения</a></li>
                    <li><a class="link link-hover" href="../services/service-design.php">Дизайн интерфейсов</a></li>
                    <li><a class="link link-hover" href="../services/service-backend.php">Backend‑разработка</a></li>
                    <li><a class="link link-hover" href="../services/service-support.php">Техническая поддержка</a></li>
                    <li><a class="link link-hover" href="../services/service-analytics.php">Аналитика и консалтинг</a></li>
                </ul>
            </div>
            <div>
                <div><a class="link link-hover" href="tel:+79956215202">8 995 6215202</a></div>
                <div class="mt-1"><a class="link link-hover" href="mailto:ruslan@onza.me">ruslan@onza.me</a></div>
                <div class="mt-1"><a class="link link-hover" href="https://t.me/siraev" target="_blank">t.me/siraev</a></div>
            </div>
            <div>
                <div class="font-semibold">Меню</div>
                <ul class="mt-2 space-y-1">
                    <li><a class="link link-hover" href="/index.php">Главная</a></li>
                    <li><a class="link link-hover" href="/services">Услуги</a></li>
                    <li><a class="link link-hover" href="/projects">Проекты</a></li>
                    <li><a class="link link-hover" href="/contacts.php">Контакты</a></li>
                    <li><a class="link link-hover" href="/vacancies.php">Вакансии</a></li>
                    <li><a class="link link-hover" href="/blog.php">Блог</a></li>
                </ul>
            </div>
        </div>
    </footer>
    
    <?php if (!empty($galleryImages)): ?>
    <!-- Fullscreen overlay for gallery -->
    <div id="gallery-overlay" class="fixed inset-0 hidden items-center justify-center bg-black/80 z-50">
        <button type="button" class="overlay-close absolute top-4 right-4 btn btn-ghost text-white" aria-label="Закрыть">✕</button>
        <button type="button" class="overlay-prev absolute left-6 top-1/2 -translate-y-1/2 btn btn-ghost text-white" aria-label="Предыдущее">‹</button>
        <img id="overlay-img" class="max-w-[92vw] max-h-[88vh] object-contain opacity-100 transition-opacity duration-300" src="" alt="Просмотр изображения" />
        <button type="button" class="overlay-next absolute right-6 top-1/2 -translate-y-1/2 btn btn-ghost text-white" aria-label="Следующее">›</button>
    </div>

    <script>
      (function() {
        const images = <?php 
          $normalizedPaths = array_map(function($img) {
            $path = trim($img['image_path']);
            return (strpos($path, '/') === 0) ? $path : '/' . $path;
          }, $galleryImages);
          echo json_encode($normalizedPaths);
        ?>;
        let currentIndex = 0;

        const mainImg = document.getElementById('gallery-main');
        const prevBtn = document.querySelector('.gallery-prev');
        const nextBtn = document.querySelector('.gallery-next');
        const thumbs = document.getElementById('gallery-thumbs');

        const overlay = document.getElementById('gallery-overlay');
        const overlayImg = document.getElementById('overlay-img');
        const overlayClose = overlay.querySelector('.overlay-close');
        const overlayPrev = overlay.querySelector('.overlay-prev');
        const overlayNext = overlay.querySelector('.overlay-next');

        function show(index) {
          currentIndex = (index + images.length) % images.length;
          const nextSrc = images[currentIndex];

          // Плавная смена основного изображения
          if (mainImg) {
            mainImg.style.opacity = '0';
          }

          // Плавная смена изображения в оверлее (если открыт)
          if (overlayImg && overlay && !overlay.classList.contains('hidden')) {
            overlayImg.style.opacity = '0';
          }

          // Обновляем активное состояние миниатюр
          if (thumbs) {
            const thumbButtons = thumbs.querySelectorAll('.gallery-thumb');
            thumbButtons.forEach((btn, idx) => {
              if (idx === currentIndex) {
                btn.classList.add('gallery-thumb-active');
              } else {
                btn.classList.remove('gallery-thumb-active');
              }
            });
          }

          // Меняем src после начала затухания
          setTimeout(() => {
            if (mainImg) {
              mainImg.src = nextSrc;
              mainImg.style.opacity = '1';
            }

            if (overlayImg && overlay && !overlay.classList.contains('hidden')) {
              overlayImg.src = nextSrc;
              overlayImg.style.opacity = '1';
            }
          }, 160);
        }

        function openOverlay() {
          overlay.classList.remove('hidden');
          overlay.classList.add('flex');
          overlayImg.src = images[currentIndex];
        }

        function closeOverlay() {
          overlay.classList.add('hidden');
          overlay.classList.remove('flex');
        }

        if (prevBtn) prevBtn.addEventListener('click', () => show(currentIndex - 1));
        if (nextBtn) nextBtn.addEventListener('click', () => show(currentIndex + 1));
        if (mainImg) mainImg.addEventListener('click', openOverlay);
        if (thumbs) thumbs.addEventListener('click', (e) => {
          const btn = e.target.closest('[data-gallery-index]');
          if (!btn) return;
          const idx = parseInt(btn.getAttribute('data-gallery-index'), 10);
          if (!Number.isFinite(idx)) return;
          show(idx);
        });
        if (overlayClose) overlayClose.addEventListener('click', closeOverlay);
        if (overlayPrev) overlayPrev.addEventListener('click', () => show(currentIndex - 1));
        if (overlayNext) overlayNext.addEventListener('click', () => show(currentIndex + 1));
        if (overlay) overlay.addEventListener('click', (e) => { if (e.target === overlay) closeOverlay(); });
        window.addEventListener('keydown', (e) => {
          if (!overlay || overlay.classList.contains('hidden')) return;
          if (e.key === 'Escape') closeOverlay();
          if (e.key === 'ArrowLeft') show(currentIndex - 1);
          if (e.key === 'ArrowRight') show(currentIndex + 1);
        });

        show(0);
      })();
    </script>
    <?php endif; ?>

    <script src="/assets/hero-anim.js" defer></script>
</body>
</html>
