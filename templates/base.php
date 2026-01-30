<!doctype html>
<html lang="ru" data-theme="corporate">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo $pageTitle; ?> - ONZA.me</title>
    <?php if ($metaDescription): ?>
    <meta name="description" content="<?php echo $metaDescription; ?>" />
    <?php endif; ?>
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
<body class="min-h-screen flex flex-col">
    <!-- Header будет вставлен через header.js -->
    <header></header>

    <main class="flex-1">
        <?php echo $pageContent; ?>
    </main>

    <?php
    // Глобальный CTA перед футером (если он не вставлен в контент вручную)
    $hasCtaInContent = isset($pageContent) && (stripos($pageContent, 'Готовы обсудить ваш проект?') !== false || strpos($pageContent, 'data-cta-wave') !== false);
    if (!$hasCtaInContent) {
        include __DIR__ . '/cta.php';
    }
    ?>

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
