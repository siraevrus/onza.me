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

    <?php include __DIR__ . '/footer.php'; ?>

    <script src="/assets/hero-anim.js" defer></script>
</body>
</html>
