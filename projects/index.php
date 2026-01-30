<?php
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$projects = $db->query("SELECT * FROM projects ORDER BY display_order ASC, id DESC")->fetchAll();
?>
<!doctype html>
<html lang="ru" data-theme="corporate">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>IT Company — Проекты</title>
    <meta name="description" content="Наши проекты: веб‑сервисы, мобильные приложения, SaaS и корпоративные системы." />
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
            <li class="font-semibold">Проекты</li>
          </ul>
        </div>
      </nav>
      <section class="container mx-auto max-w-7xl px-4 py-12 bw-section">
        <h1 class="text-4xl font-extrabold">Проекты</h1>
        <p class="mt-3 max-w-2xl">Подборка реализованных решений в e‑commerce, финтехе, логистике и медиа.</p>
      </section>

      <!-- Несколько проектов -->
      <section class="bw-section">
        <div class="container mx-auto max-w-7xl px-4 py-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <?php if (empty($projects)): ?>
            <div class="col-span-full text-center py-12">
              <p class="text-gray-500">Проекты пока не добавлены.</p>
            </div>
          <?php else: ?>
            <?php foreach ($projects as $project): 
              // Обрабатываем ссылку: конвертируем .html в .php
              $link = trim($project['link']);
              
              // Если ссылка не внешняя (не начинается с http/https)
              if (!preg_match('/^https?:\/\//', $link)) {
                // Если ссылка заканчивается на .html, пробуем .php версию
                if (preg_match('/\.html$/', $link)) {
                  $phpLink = preg_replace('/\.html$/', '.php', $link);
                  $phpFile = __DIR__ . '/' . $phpLink;
                  $htmlFile = __DIR__ . '/' . $link;
                  
                  // Используем PHP версию если существует, иначе HTML
                  if (file_exists($phpFile)) {
                    $link = '/projects/' . $phpLink;
                  } elseif (file_exists($htmlFile)) {
                    // Оставляем HTML если PHP не существует
                    $link = '/projects/' . $link;
                  } else {
                    // Если ни PHP ни HTML файл не существует, используем PHP версию
                    // Файл может быть создан позже, но проект должен отображаться
                    $link = '/projects/' . $phpLink;
                  }
                } elseif (!preg_match('/\.(php|html)$/', $link)) {
                  // Если ссылка без расширения, пробуем .php
                  $phpFile = __DIR__ . '/' . $link . '.php';
                  $htmlFile = __DIR__ . '/' . $link . '.html';
                  
                  if (file_exists($phpFile)) {
                    $link = '/projects/' . $link . '.php';
                  } elseif (file_exists($htmlFile)) {
                    $link = '/projects/' . $link . '.html';
                  } else {
                    // Если файл не существует, добавляем .php (файл может быть создан позже)
                    $link = '/projects/' . $link . '.php';
                  }
                } else {
                  // Если ссылка уже с расширением .php или .html, добавляем абсолютный путь
                  $link = '/projects/' . $link;
                }
              }
              
              $link = htmlspecialchars($link);
            ?>
              <a class="card card-zoom" href="<?php echo $link; ?>">
                <figure class="bg-white h-44 flex items-center justify-center">
                  <img src="<?php echo htmlspecialchars($project['logo_path']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="h-12 w-auto object-contain" />
                </figure>
                <div class="card-body flex flex-col">
                  <h3 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                  <?php if (!empty($project['subtitle'])): ?>
                    <p class="text-sm mb-4"><?php echo htmlspecialchars($project['subtitle']); ?></p>
                  <?php else: ?>
                    <p class="text-sm mb-4"><?php echo htmlspecialchars($project['description']); ?></p>
                  <?php endif; ?>
                  <div class="mt-auto flex flex-wrap gap-2 text-xs">
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
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>

    </main>

    <?php include __DIR__ . '/../templates/cta.php'; ?>

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
          <div class="mt-1"><a class="link link-hover" href="https://t.me/siraev" target="_blank">t.me/siraev</a></div>        </div>
        <div>
          <div class="font-semibold">Меню</div>
          <ul class="mt-2 space-y-1">
            <li><a class="link link-hover" href="index.php">Главная</a></li>
            <li><a class="link link-hover" href="../services">Услуги</a></li>
            <li><a class="link link-hover" href="../projects">Проекты</a></li>
            <li><a class="link link-hover" href="contacts.php">Контакты</a></li>
            <li><a class="link link-hover" href="vacancies.php">Вакансии</a></li>
            <li><a class="link link-hover" href="blog.php">Блог</a></li>
          </ul>
        </div>
      </div>
    </footer>
  </body>
</html>
