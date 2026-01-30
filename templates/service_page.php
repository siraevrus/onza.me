<!doctype html>
<html lang="ru" data-theme="corporate">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo htmlspecialchars($service['title'] ?? 'Услуга'); ?> - ONZA.me</title>
    <meta name="description" content="<?php echo htmlspecialchars($service['subtitle'] ?? ''); ?>" />
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
    <!-- Header будет вставлен через header.js -->
    <header></header>

    <main class="flex-1">
        <!-- Хлебные крошки -->
        <nav class="container mx-auto max-w-7xl px-4 pt-6 text-sm" aria-label="Хлебные крошки">
            <div class="breadcrumbs">
                <ul>
                    <li><a href="/index.php">Главная</a></li>
                    <li><a href="/services">Услуги</a></li>
                    <li class="font-semibold"><?php echo htmlspecialchars($service['title'] ?? 'Услуга'); ?></li>
                </ul>
            </div>
        </nav>

        <!-- Заголовок услуги -->
        <section class="container mx-auto max-w-7xl px-4 py-12 bw-section">
            <h1 class="text-4xl font-extrabold"><?php echo htmlspecialchars($service['title'] ?? 'Услуга'); ?></h1>
            <?php if (!empty($service['detail_subtitle'])): ?>
                <p class="mt-3 max-w-3xl"><?php echo htmlspecialchars($service['detail_subtitle']); ?></p>
            <?php endif; ?>
        </section>

        <!-- Блоки контента услуги -->
        <?php 
        // Убеждаемся, что переменная $blocks определена
        if (!isset($blocks)) {
            $blocks = [];
        }
        
        if (is_array($blocks) && !empty($blocks)): 
            $isFirstBlockWithBackground = true;
            foreach ($blocks as $block): 
                $hasBackground = isset($block['has_background']) ? (int)$block['has_background'] : 0;
                $blockTitle = htmlspecialchars($block['title'] ?? '');
                $blockContent = $block['content'] ?? '';
                
                if ($hasBackground == 1): 
                    // Для первого блока с подложкой: верхний отступ больше (48px), для остальных - добавляем margin-top 25px
                    $paddingClass = $isFirstBlockWithBackground ? 'pt-12 pb-0' : 'pt-0 pb-0';
                    $marginTop = $isFirstBlockWithBackground ? '' : 'style="margin-top: 25px;"';
                    $isFirstBlockWithBackground = false;
        ?>
                    <!-- Блок с подложкой (белая карточка) -->
                    <section class="container mx-auto max-w-7xl px-4 <?php echo $paddingClass; ?>" <?php echo $marginTop; ?>>
                        <div class="bg-white rounded-2xl border border-black/10 p-8 md:p-10">
                            <?php if (!empty($blockTitle)): ?>
                                <h2 class="text-2xl font-bold mb-4"><?php echo $blockTitle; ?></h2>
                            <?php endif; ?>
                            <div class="prose prose-lg max-w-none">
                                <?php echo $blockContent; ?>
                            </div>
                        </div>
                    </section>
        <?php else: ?>
                    <!-- Блок без подложки (белый фон) -->
                    <section class="container mx-auto max-w-7xl px-4 py-12">
                        <?php if (!empty($blockTitle)): ?>
                            <h2 class="text-2xl font-bold mb-4"><?php echo $blockTitle; ?></h2>
                        <?php endif; ?>
                        <div class="prose prose-lg max-w-none">
                            <?php echo $blockContent; ?>
                        </div>
                    </section>
        <?php 
                endif;
            endforeach;
        endif; 
        ?>
    </main>

    <?php include __DIR__ . '/cta.php'; ?>

    <!-- Footer -->
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
                    <li><a class="link link-hover" href="/services/service-mobile.php">Мобильные приложения</a></li>
                    <li><a class="link link-hover" href="/services/service-design.php">Дизайн интерфейсов</a></li>
                    <li><a class="link link-hover" href="/services/service-backend.php">Backend‑разработка</a></li>
                    <li><a class="link link-hover" href="/services/service-support.php">Техническая поддержка</a></li>
                    <li><a class="link link-hover" href="/services/service-analytics.php">Аналитика и консалтинг</a></li>
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

    <script src="/assets/hero-anim.js" defer></script>
</body>
</html>
