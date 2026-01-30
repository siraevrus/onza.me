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
            // Разделяем блоки на те, что с подложкой и без
            $blocksWithBg = [];
            $blocksWithoutBg = [];
            
            foreach ($blocks as $block): 
                $hasBackground = isset($block['has_background']) ? (int)$block['has_background'] : 0;
                if ($hasBackground == 1) {
                    $blocksWithBg[] = $block;
                } else {
                    $blocksWithoutBg[] = $block;
                }
            endforeach;
            
            // Выводим блоки с подложкой в 2 колонки
            if (!empty($blocksWithBg)): 
        ?>
            <section class="container mx-auto max-w-7xl px-4 pt-12 pb-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($blocksWithBg as $block): 
                        $blockTitle = htmlspecialchars($block['title'] ?? '');
                        $blockContent = $block['content'] ?? '';
                    ?>
                        <div class="bg-white rounded-2xl border border-black/10 p-8 md:p-10">
                            <?php if (!empty($blockTitle)): ?>
                                <h2 class="text-2xl font-bold mb-4"><?php echo $blockTitle; ?></h2>
                            <?php endif; ?>
                            <div class="prose prose-lg max-w-none">
                                <?php echo $blockContent; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php 
            endif;
            
            // Выводим блоки без подложки (полная ширина)
            foreach ($blocksWithoutBg as $block): 
                $blockTitle = htmlspecialchars($block['title'] ?? '');
                $blockContent = $block['content'] ?? '';
        ?>
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
            endforeach;
        endif; 
        ?>
    </main>

    <?php include __DIR__ . '/cta.php'; ?>

    <?php include __DIR__ . '/footer.php'; ?>

    <script src="/assets/hero-anim.js" defer></script>
</body>
</html>
