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
    
    <?php include __DIR__ . '/templates/footer.php'; ?>
</body>
</html>
