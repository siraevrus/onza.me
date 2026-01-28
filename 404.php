<!doctype html>
<html lang="ru" data-theme="corporate">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>404 - Страница не найдена</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geologica:wght,CRSV@100..900,0&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
    <link href="assets/styles.css" rel="stylesheet" />
    <script src="assets/header.js" defer></script>
</head>
<body class="min-h-screen flex flex-col bg-grid">
    <header></header>
    
    <main class="flex-1">
        <section class="container mx-auto max-w-7xl px-4 py-32">
            <div class="text-center">
                <h1 class="text-6xl font-bold mb-4">404</h1>
                <h2 class="text-3xl font-bold mb-4">Страница не найдена</h2>
                <p class="text-lg mb-8">Запрашиваемая страница не существует или была перемещена.</p>
                <div class="flex gap-4 justify-center">
                    <a href="index.php" class="btn btn-primary">Вернуться на главную</a>
                    <a href="projects.php" class="btn btn-outline">Посмотреть проекты</a>
                </div>
            </div>
        </section>
    </main>
    
    <footer class="bg-white">
        <div class="container mx-auto max-w-7xl px-4 py-10 grid gap-6 md:grid-cols-4 items-start">
            <div>
                <img src="assets/image/logo.svg" alt="ONZA.ME" class="logo-img mb-2" />
                <div class="mt-2">© Onza.me</div>
                <div class="mt-1">Все права защищены</div>
            </div>
            <div>
                <div class="font-semibold">Услуги</div>
                <ul class="mt-2 space-y-1">
                    <li><a class="link link-hover" href="service-mobile.php">Мобильные приложения</a></li>
                    <li><a class="link link-hover" href="service-design.php">Дизайн интерфейсов</a></li>
                    <li><a class="link link-hover" href="service-backend.php">Backend‑разработка</a></li>
                    <li><a class="link link-hover" href="service-support.php">Техническая поддержка</a></li>
                    <li><a class="link link-hover" href="service-analytics.php">Аналитика и консалтинг</a></li>
                </ul>
            </div>
            <div>
                <div><a class="link link-hover" href="tel:+79805422655">8 9805422655</a></div>
                <div class="mt-1"><a class="link link-hover" href="mailto:hello.me">hello.me</a></div>
            </div>
            <div>
                <div class="font-semibold">Меню</div>
                <ul class="mt-2 space-y-1">
                    <li><a class="link link-hover" href="index.php">Главная</a></li>
                    <li><a class="link link-hover" href="services.php">Услуги</a></li>
                    <li><a class="link link-hover" href="projects.php">Проекты</a></li>
                    <li><a class="link link-hover" href="contacts.php">Контакты</a></li>
                    <li><a class="link link-hover" href="vacancies.php">Вакансии</a></li>
                    <li><a class="link link-hover" href="blog.php">Блог</a></li>
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>
