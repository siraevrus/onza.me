<?php
/**
 * Общий футер сайта
 * Подключается через include __DIR__ . '/templates/footer.php' или include __DIR__ . '/footer.php'
 */

// Функция для рендеринга списка услуг в футере
if (!function_exists('renderFooterServicesList')) {
    function renderFooterServicesList() {
        require_once __DIR__ . '/../config/database.php';
        try {
            $db = getDB();
            $services = $db->query("SELECT id, title, slug FROM services WHERE is_active = 1 ORDER BY display_order ASC, id ASC LIMIT 10")->fetchAll();
            if (empty($services)) {
                return '';
            }
            ob_start();
            ?>
            <ul class="mt-2 space-y-1">
                <?php foreach ($services as $svc): ?>
                    <li><a class="link link-hover" href="/services/<?php echo htmlspecialchars($svc['slug']); ?>.php"><?php echo htmlspecialchars($svc['title']); ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php
            return ob_get_clean();
        } catch (Exception $e) {
            return '';
        }
    }
}

// Функция для генерации HTML футера
if (!function_exists('generateFooterHtml')) {
    function generateFooterHtml() {
        $footerServicesHtml = renderFooterServicesList();
        ob_start();
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
            <?php echo $footerServicesHtml ?: '<ul class="mt-2 space-y-1"><li><a class="link link-hover" href="/services">Услуги</a></li></ul>'; ?>
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
        <?php
        return ob_get_clean();
    }
}

// Функция для замены футера в HTML строке
if (!function_exists('replaceFooterInHtml')) {
    function replaceFooterInHtml($html) {
        // Удаляем старый футер
        $html = preg_replace('/<footer[^>]*>[\s\S]*?<\/footer>/i', '<!--FOOTER_PLACEHOLDER-->', $html, 1);
        // Вставляем новый футер
        $footerHtml = generateFooterHtml();
        $html = str_replace('<!--FOOTER_PLACEHOLDER-->', $footerHtml, $html);
        return $html;
    }
}

// Выводим футер только при прямом include (не при вызове функций)
// Проверяем, не вызывается ли файл только для загрузки функций
if (!isset($GLOBALS['_footer_functions_only'])) {
    echo generateFooterHtml();
}
