<?php
require_once __DIR__ . '/config/auth.php';
requireAuth();

$db = getDB();
$message = '';
$messageType = '';
$activeTab = $_GET['tab'] ?? 'projects';
$editProjectId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

// Получаем проект для редактирования, если указан ID
$editProject = null;
if ($editProjectId > 0 && $activeTab === 'projects') {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$editProjectId]);
    $editProject = $stmt->fetch();
    if (!$editProject) {
        $editProjectId = 0;
        $message = 'Проект не найден';
        $messageType = 'error';
    }
}

// Обработка удаления проекта
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && $activeTab === 'projects') {
    $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
    if ($stmt->execute([$_GET['delete']])) {
        $message = 'Проект успешно удален';
        $messageType = 'success';
    } else {
        $message = 'Ошибка при удалении проекта';
        $messageType = 'error';
    }
}

// Получаем все проекты
$projects = $db->query("SELECT * FROM projects ORDER BY display_order ASC, id DESC")->fetchAll();

// Получаем все страницы
$pages = $db->query("SELECT id, slug, title, is_active, created_at, updated_at FROM pages ORDER BY slug ASC")->fetchAll();

// Получаем все вакансии
$vacancies = $db->query("SELECT * FROM vacancies ORDER BY display_order ASC, id DESC")->fetchAll();

// Получаем статьи блога
$blogPosts = $db->query("SELECT * FROM blog_posts ORDER BY display_order ASC, id DESC")->fetchAll();
?>
<!doctype html>
<html lang="ru" data-theme="corporate">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Админ-панель — Управление контентом</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geologica:wght,CRSV@100..900,0&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
    <link href="assets/styles.css" rel="stylesheet" />
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    
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
<body class="min-h-screen bg-grid">
    <div class="drawer lg:drawer-open">
        <input id="admin-drawer" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content">
            <div class="container mx-auto max-w-7xl px-4 py-8">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-3">
                        <label for="admin-drawer" class="btn btn-outline lg:hidden">Меню</label>
                        <h1 class="text-3xl font-bold">Админ-панель</h1>
                    </div>
                    <div class="flex gap-3">
                        <a href="index.php" class="btn btn-outline" target="_blank">Посмотреть сайт</a>
                        <a href="logout.php" class="btn btn-outline">Выйти</a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?> mb-4">
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

        <?php if ($activeTab === 'projects'): ?>
            <?php if ($editProjectId > 0 && $editProject): ?>
                <!-- Страница редактирования проекта -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold">Редактировать проект</h2>
                        <a href="?tab=projects" class="btn btn-outline">← Назад к списку</a>
                    </div>
                    
                    <div class="card bg-white">
                        <div class="card-body">
                            <form id="editProjectPageForm" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="edit" />
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editProject['id']); ?>" />
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div class="form-control">
                                        <label class="label">
                                            <span class="label-text">Название проекта *</span>
                                        </label>
                                        <input type="text" name="title" value="<?php echo htmlspecialchars($editProject['title']); ?>" class="input input-bordered" required />
                                    </div>
                                    
                                    <div class="form-control">
                                        <label class="label">
                                            <span class="label-text">Ссылка на страницу проекта *</span>
                                        </label>
                                        <input type="text" name="link" value="<?php echo htmlspecialchars($editProject['link']); ?>" class="input input-bordered" required />
                                    </div>
                                </div>
                                
                                <div class="form-control mb-4">
                                    <label class="label">
                                        <span class="label-text">Подзаголовок</span>
                                        <span class="label-text-alt">Краткое описание для карточки проекта</span>
                                    </label>
                                    <input type="text" name="subtitle" value="<?php echo htmlspecialchars($editProject['subtitle'] ?? ''); ?>" class="input input-bordered" placeholder="Краткое описание проекта" />
                                </div>
                                
                                <div class="form-control mb-4">
                                    <label class="label">
                                        <span class="label-text">Описание *</span>
                                    </label>
                                    <textarea name="description" id="edit_project_description_page" class="textarea textarea-bordered" rows="5" required><?php echo htmlspecialchars($editProject['description']); ?></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div class="form-control">
                                        <label class="label">
                                            <span class="label-text">Что сделали</span>
                                            <span class="label-text-alt">Каждый пункт с новой строки</span>
                                        </label>
                                        <textarea name="what_done" class="textarea textarea-bordered" rows="8"><?php echo htmlspecialchars($editProject['what_done'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-control">
                                        <label class="label">
                                            <span class="label-text">Технологии</span>
                                            <span class="label-text-alt">Каждый пункт с новой строки</span>
                                        </label>
                                        <textarea name="technologies" class="textarea textarea-bordered" rows="8"><?php echo htmlspecialchars($editProject['technologies'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div class="form-control">
                                        <label class="label">
                                            <span class="label-text">Логотип проекта *</span>
                                        </label>
                                        <div class="flex flex-col gap-2">
                                            <input type="file" id="edit_page_logo_upload" name="logo_upload" accept="image/*" class="file-input file-input-bordered" />
                                            <input type="hidden" id="edit_page_logo_path" name="logo_path" value="<?php echo htmlspecialchars($editProject['logo_path']); ?>" />
                                            <div id="edit_page_logo_preview" class="mt-2">
                                                <div class="text-sm text-gray-500 mb-1">Текущий логотип:</div>
                                                <img id="edit_page_logo_preview_img" src="<?php echo htmlspecialchars($editProject['logo_path']); ?>" alt="Preview" class="max-w-32 max-h-32 object-contain border border-gray-300 rounded" />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-control">
                                        <label class="label">
                                            <span class="label-text">Теги (через запятую) *</span>
                                        </label>
                                        <input type="text" name="tags" value="<?php echo htmlspecialchars($editProject['tags']); ?>" class="input input-bordered" required />
                                    </div>
                                </div>
                                
                                <div class="form-control mb-4">
                                    <label class="label">
                                        <span class="label-text">Сайт</span>
                                        <span class="label-text-alt">URL сайта проекта (например: www.example.com)</span>
                                    </label>
                                    <input type="text" name="website" value="<?php echo htmlspecialchars($editProject['website'] ?? ''); ?>" class="input input-bordered" placeholder="www.example.com" />
                                </div>
                                
                                <div class="form-control mb-4">
                                    <label class="label">
                                        <span class="label-text">Порядок отображения</span>
                                    </label>
                                    <input type="number" name="display_order" value="<?php echo htmlspecialchars($editProject['display_order']); ?>" class="input input-bordered" />
                                </div>
                                
                                <div class="flex gap-4">
                                    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                                    <a href="?tab=projects" class="btn btn-outline">Отмена</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Раздел проектов -->
                <div class="card bg-white mb-6">
                    <div class="card-body">
                        <h2 class="card-title text-xl mb-4">Добавить новый проект</h2>
                        <form id="projectForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add" />
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Название проекта *</span>
                                </label>
                                <input type="text" name="title" class="input input-bordered" required />
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Ссылка на страницу проекта *</span>
                                </label>
                                <input type="text" name="link" class="input input-bordered" placeholder="project-name.php" required />
                            </div>
                        </div>
                        
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Подзаголовок</span>
                                <span class="label-text-alt">Краткое описание для карточки проекта</span>
                            </label>
                            <input type="text" name="subtitle" class="input input-bordered" placeholder="Краткое описание проекта" />
                        </div>
                        
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Описание *</span>
                            </label>
                            <textarea name="description" id="project_description" class="textarea textarea-bordered" rows="3" required></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Что сделали</span>
                                    <span class="label-text-alt">Каждый пункт с новой строки</span>
                                </label>
                                <textarea name="what_done" class="textarea textarea-bordered" rows="5" placeholder="Спроектировали дизайн интерфейса&#10;Разработали backend&#10;Подготовили контент"></textarea>
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Технологии</span>
                                    <span class="label-text-alt">Каждый пункт с новой строки</span>
                                </label>
                                <textarea name="technologies" class="textarea textarea-bordered" rows="5" placeholder="Backend: Laravel, PostgreSQL&#10;Frontend: Vue/React, Tailwind&#10;DevOps: Docker, CI/CD"></textarea>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Логотип проекта *</span>
                                </label>
                                <div class="flex flex-col gap-2">
                                    <input type="file" id="logo_upload" name="logo_upload" accept="image/*" class="file-input file-input-bordered" required />
                                    <input type="hidden" id="logo_path" name="logo_path" />
                                    <div id="logo_preview" class="mt-2 hidden">
                                        <div class="text-sm text-gray-500 mb-1">Предпросмотр:</div>
                                        <img id="logo_preview_img" src="" alt="Preview" class="max-w-32 max-h-32 object-contain border border-gray-300 rounded" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Теги (через запятую) *</span>
                                </label>
                                <input type="text" name="tags" class="input input-bordered" placeholder="web, ux, android" required />
                            </div>
                        </div>
                        
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Сайт</span>
                                <span class="label-text-alt">URL сайта проекта (например: www.example.com)</span>
                            </label>
                            <input type="text" name="website" class="input input-bordered" placeholder="www.example.com" />
                        </div>
                        
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Порядок отображения</span>
                            </label>
                            <input type="number" name="display_order" class="input input-bordered" value="0" />
                        </div>
                        
                        <div class="form-control">
                            <button type="submit" class="btn btn-primary">Добавить проект</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card bg-white">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Список проектов (<?php echo count($projects); ?>)</h2>
                    
                    <?php if (empty($projects)): ?>
                        <p class="text-gray-500">Проектов пока нет. Добавьте первый проект выше.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="table table-zebra">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Название</th>
                                        <th>Описание</th>
                                        <th>Ссылка</th>
                                        <th>Теги</th>
                                        <th>Порядок</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $project): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($project['id']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($project['title']); ?></strong></td>
                                            <td><?php echo htmlspecialchars(mb_substr($project['description'], 0, 50)) . '...'; ?></td>
                                            <td><a href="<?php echo htmlspecialchars($project['link']); ?>" target="_blank" class="link"><?php echo htmlspecialchars($project['link']); ?></a></td>
                                            <td><?php echo htmlspecialchars($project['tags']); ?></td>
                                            <td><?php echo htmlspecialchars($project['display_order']); ?></td>
                                            <td>
                                                <a href="?tab=projects&edit=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline">Редактировать</a>
                                                <button type="button" class="btn btn-sm btn-info js-open-gallery" data-project-id="<?php echo (int)$project['id']; ?>">Галерея</button>
                                                <a href="?tab=projects&delete=<?php echo $project['id']; ?>" class="btn btn-sm btn-error" onclick="return confirm('Вы уверены, что хотите удалить этот проект?')">Удалить</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php elseif ($activeTab === 'pages'): ?>
            <!-- Раздел страниц -->
            <div class="card bg-white mb-6">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Создать новую страницу</h2>
                    <form id="pageForm">
                        <input type="hidden" name="action" value="add" />
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">URL (slug) *</span>
                                    <span class="label-text-alt">Например: services, contacts</span>
                                </label>
                                <input type="text" name="slug" id="page_slug" class="input input-bordered" placeholder="services" required />
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Заголовок страницы *</span>
                                </label>
                                <input type="text" name="title" id="page_title" class="input input-bordered" required />
                            </div>
                        </div>
                        
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Meta описание</span>
                            </label>
                            <textarea name="meta_description" id="page_meta_description" class="textarea textarea-bordered" rows="2"></textarea>
                        </div>
                        
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Содержимое страницы (HTML) *</span>
                            </label>
                            <textarea name="content" id="page_content" class="textarea textarea-bordered" rows="15" required></textarea>
                        </div>
                        
                        <div class="form-control">
                            <button type="submit" class="btn btn-primary">Создать страницу</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card bg-white">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Список страниц (<?php echo count($pages); ?>)</h2>
                    
                    <?php if (empty($pages)): ?>
                        <p class="text-gray-500">Страниц пока нет. Создайте первую страницу выше.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="table table-zebra">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>URL</th>
                                        <th>Заголовок</th>
                                        <th>Статус</th>
                                        <th>Обновлено</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pages as $page): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($page['id']); ?></td>
                                            <td><code><?php echo htmlspecialchars($page['slug']); ?></code></td>
                                            <td><strong><?php echo htmlspecialchars($page['title']); ?></strong></td>
                                            <td>
                                                <span class="badge <?php echo $page['is_active'] ? 'badge-success' : 'badge-error'; ?>">
                                                    <?php echo $page['is_active'] ? 'Активна' : 'Неактивна'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($page['updated_at'])); ?></td>
                                            <td>
                                                <button onclick="window.editPage(<?php echo htmlspecialchars(json_encode($page)); ?>)" class="btn btn-sm btn-outline">Редактировать</button>
                                                <a href="<?php echo htmlspecialchars($page['slug']); ?>.php" target="_blank" class="btn btn-sm btn-info">Просмотр</a>
                                                <button onclick="window.deletePage(<?php echo $page['id']; ?>)" class="btn btn-sm btn-error">Удалить</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($activeTab === 'vacancies'): ?>
            <!-- Раздел вакансий -->
            <div class="card bg-white mb-6">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Добавить вакансию</h2>
                    <form id="vacancyForm">
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Заголовок *</span>
                            </label>
                            <input type="text" name="title" class="input input-bordered" required />
                        </div>
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Подзаголовок *</span>
                            </label>
                            <textarea name="subtitle" class="textarea textarea-bordered" rows="10" required></textarea>
                        </div>
                        <div class="form-control">
                            <button type="submit" class="btn btn-primary">Добавить вакансию</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card bg-white">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Список вакансий (<?php echo count($vacancies); ?>)</h2>
                    <?php if (empty($vacancies)): ?>
                        <p class="text-gray-500">Вакансий пока нет.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="table table-zebra">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Заголовок</th>
                                        <th>Подзаголовок</th>
                                        <th>Создано</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vacancies as $v): ?>
                                        <tr>
                                            <td><?php echo (int)$v['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($v['title']); ?></strong></td>
                                            <td><?php echo htmlspecialchars(mb_substr($v['subtitle'], 0, 80)); ?><?php echo mb_strlen($v['subtitle']) > 80 ? '…' : ''; ?></td>
                                            <td><?php echo !empty($v['created_at']) ? date('d.m.Y H:i', strtotime($v['created_at'])) : '-'; ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline" onclick="window.editVacancy(<?php echo htmlspecialchars(json_encode($v)); ?>)">Редактировать</button>
                                                <button type="button" class="btn btn-sm btn-error" data-vacancy-delete="<?php echo (int)$v['id']; ?>">Удалить</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($activeTab === 'blog'): ?>
            <!-- Раздел блога -->
            <div class="card bg-white mb-6">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Добавить статью</h2>
                    <form id="blogPostForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Превью изображение *</span>
                                </label>
                                <div class="flex flex-col gap-2">
                                    <input type="file" id="blog_image_upload" accept="image/*" class="file-input file-input-bordered" required />
                                    <input type="hidden" id="blog_image_path" name="image_path" />
                                    <div id="blog_image_preview" class="mt-2 hidden">
                                        <div class="text-sm text-gray-500 mb-1">Предпросмотр:</div>
                                        <img id="blog_image_preview_img" src="" alt="Preview" class="max-w-64 max-h-40 object-cover border border-gray-300 rounded" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Порядок отображения</span>
                                </label>
                                <input type="number" name="display_order" class="input input-bordered" value="0" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Заголовок *</span>
                                </label>
                                <input type="text" name="title" class="input input-bordered" required />
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Подзаголовок *</span>
                                </label>
                                <input type="text" name="subtitle" class="input input-bordered" required />
                            </div>
                        </div>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Контент статьи *</span>
                            </label>
                            <textarea name="content" id="blog_content" class="textarea textarea-bordered" rows="12" required></textarea>
                        </div>

                        <div class="form-control">
                            <button type="submit" class="btn btn-primary">Добавить статью</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card bg-white">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Список статей (<?php echo count($blogPosts); ?>)</h2>
                    <?php if (empty($blogPosts)): ?>
                        <p class="text-gray-500">Статей пока нет.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="table table-zebra">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Превью</th>
                                        <th>Заголовок</th>
                                        <th>Подзаголовок</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blogPosts as $p): ?>
                                        <tr>
                                            <td><?php echo (int)$p['id']; ?></td>
                                            <td>
                                                <img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="" class="h-12 w-20 object-cover border border-gray-300 rounded" />
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($p['title']); ?></strong></td>
                                            <td><?php echo htmlspecialchars(mb_substr($p['subtitle'], 0, 80)); ?><?php echo mb_strlen($p['subtitle']) > 80 ? '…' : ''; ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline" onclick="window.editBlogPost(<?php echo htmlspecialchars(json_encode($p)); ?>)">Редактировать</button>
                                                <button type="button" class="btn btn-sm btn-error" data-blog-delete="<?php echo (int)$p['id']; ?>">Удалить</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <span>Неизвестная вкладка.</span>
            </div>
        <?php endif; ?>
            </div>
        </div>

        <div class="drawer-side">
            <label for="admin-drawer" class="drawer-overlay"></label>
            <aside class="w-72 min-h-full bg-white border-r border-black">
                <div class="p-4 border-b border-black">
                    <div class="font-bold text-lg">Разделы</div>
                    <div class="text-xs text-gray-500">Управление контентом</div>
                </div>
                <ul class="menu p-4 gap-1">
                    <li>
                        <a href="?tab=projects" class="<?php echo $activeTab === 'projects' ? 'active' : ''; ?>" <?php echo $activeTab === 'projects' ? 'aria-current="page"' : ''; ?>>
                            Проекты
                        </a>
                    </li>
                    <li>
                        <a href="?tab=pages" class="<?php echo $activeTab === 'pages' ? 'active' : ''; ?>" <?php echo $activeTab === 'pages' ? 'aria-current="page"' : ''; ?>>
                            Страницы
                        </a>
                    </li>
                    <li>
                        <a href="?tab=vacancies" class="<?php echo $activeTab === 'vacancies' ? 'active' : ''; ?>" <?php echo $activeTab === 'vacancies' ? 'aria-current="page"' : ''; ?>>
                            Вакансии
                        </a>
                    </li>
                    <li>
                        <a href="?tab=blog" class="<?php echo $activeTab === 'blog' ? 'active' : ''; ?>" <?php echo $activeTab === 'blog' ? 'aria-current="page"' : ''; ?>>
                            Блог
                        </a>
                    </li>
                </ul>
            </aside>
        </div>
    </div>
    
    <!-- Модальное окно для редактирования проекта -->
    <dialog id="editProjectModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Редактировать проект</h3>
            <form id="editProjectForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit" />
                <input type="hidden" name="id" id="edit_project_id" />
                
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Название проекта *</span>
                    </label>
                    <input type="text" name="title" id="edit_project_title" class="input input-bordered" required />
                </div>
                
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Ссылка на страницу проекта *</span>
                    </label>
                    <input type="text" name="link" id="edit_project_link" class="input input-bordered" required />
                </div>
                
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Подзаголовок</span>
                        <span class="label-text-alt">Краткое описание для карточки проекта</span>
                    </label>
                    <input type="text" name="subtitle" id="edit_project_subtitle" class="input input-bordered" placeholder="Краткое описание проекта" />
                </div>
                
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Описание *</span>
                    </label>
                    <textarea name="description" id="edit_project_description" class="textarea textarea-bordered" rows="3" required></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Что сделали</span>
                            <span class="label-text-alt">Каждый пункт с новой строки</span>
                        </label>
                        <textarea name="what_done" id="edit_project_what_done" class="textarea textarea-bordered" rows="5"></textarea>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Технологии</span>
                            <span class="label-text-alt">Каждый пункт с новой строки</span>
                        </label>
                        <textarea name="technologies" id="edit_project_technologies" class="textarea textarea-bordered" rows="5"></textarea>
                    </div>
                </div>
                
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Логотип проекта *</span>
                    </label>
                    <div class="flex flex-col gap-2">
                        <input type="file" id="edit_logo_upload" name="logo_upload" accept="image/*" class="file-input file-input-bordered" />
                        <input type="hidden" id="edit_project_logo_path" name="logo_path" />
                        <div id="edit_logo_preview" class="mt-2">
                            <div class="text-sm text-gray-500 mb-1">Текущий логотип:</div>
                            <img id="edit_logo_preview_img" src="" alt="Preview" class="max-w-32 max-h-32 object-contain border border-gray-300 rounded" />
                        </div>
                    </div>
                </div>
                
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Теги (через запятую) *</span>
                    </label>
                    <input type="text" name="tags" id="edit_project_tags" class="input input-bordered" required />
                </div>
                
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Сайт</span>
                        <span class="label-text-alt">URL сайта проекта (например: www.example.com)</span>
                    </label>
                    <input type="text" name="website" id="edit_project_website" class="input input-bordered" placeholder="www.example.com" />
                </div>
                
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Порядок отображения</span>
                    </label>
                    <input type="number" name="display_order" id="edit_project_display_order" class="input input-bordered" />
                </div>
                
                <div class="modal-action">
                    <button type="button" onclick="document.getElementById('editProjectModal').close()" class="btn btn-outline">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
    
    <!-- Модальное окно для редактирования страницы -->
    <dialog id="editPageModal" class="modal modal-lg">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Редактировать страницу</h3>
            <form id="editPageForm">
                <input type="hidden" name="action" value="edit" />
                <input type="hidden" name="id" id="edit_page_id" />
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">URL (slug) *</span>
                        </label>
                        <input type="text" name="slug" id="edit_page_slug" class="input input-bordered" required />
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Заголовок страницы *</span>
                        </label>
                        <input type="text" name="title" id="edit_page_title" class="input input-bordered" required />
                    </div>
                </div>
                
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Meta описание</span>
                    </label>
                    <textarea name="meta_description" id="edit_page_meta_description" class="textarea textarea-bordered" rows="2"></textarea>
                </div>
                
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Содержимое страницы (HTML) *</span>
                    </label>
                    <textarea name="content" id="edit_page_content" class="textarea textarea-bordered" rows="15" required></textarea>
                </div>
                
                <div class="modal-action">
                    <button type="button" onclick="document.getElementById('editPageModal').close()" class="btn btn-outline">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
    
    <!-- Модальное окно для управления галереей -->
    <dialog id="galleryModal" class="modal modal-lg">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Управление галереей проекта</h3>
            <div id="gallery-content">
                <div class="mb-4">
                    <label class="label">
                        <span class="label-text">Загрузить изображения (можно выбрать несколько)</span>
                    </label>
                    <input type="file" id="gallery_image_upload" accept="image/*" multiple class="file-input file-input-bordered w-full" />
                    <input type="hidden" id="gallery_project_id" />
                    <div id="upload-progress" class="mt-2 hidden">
                        <div class="text-sm text-gray-500">Загрузка изображений...</div>
                        <progress id="upload-progress-bar" class="progress progress-primary w-full" value="0" max="100"></progress>
                        <div id="upload-status" class="text-xs mt-1"></div>
                    </div>
                </div>
                <div id="gallery_images_list" class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                    <!-- Изображения будут загружены через JavaScript -->
                </div>
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-outline" data-gallery-close>Закрыть</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <!-- Модальное окно для редактирования статьи блога -->
    <dialog id="editBlogPostModal" class="modal modal-lg">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Редактировать статью</h3>
            <form id="editBlogPostForm">
                <input type="hidden" name="id" id="edit_blog_id" />
                <input type="hidden" name="image_path" id="edit_blog_image_path" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Превью изображение</span>
                        </label>
                        <div class="flex flex-col gap-2">
                            <input type="file" id="edit_blog_image_upload" accept="image/*" class="file-input file-input-bordered" />
                            <div class="text-xs text-gray-500">Если не загружать новое — останется текущее.</div>
                            <img id="edit_blog_image_preview_img" src="" alt="Preview" class="max-w-64 max-h-40 object-cover border border-gray-300 rounded" />
                        </div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Порядок отображения</span>
                        </label>
                        <input type="number" name="display_order" id="edit_blog_display_order" class="input input-bordered" value="0" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Заголовок *</span>
                        </label>
                        <input type="text" name="title" id="edit_blog_title" class="input input-bordered" required />
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Подзаголовок *</span>
                        </label>
                        <input type="text" name="subtitle" id="edit_blog_subtitle" class="input input-bordered" required />
                    </div>
                </div>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Контент статьи *</span>
                    </label>
                    <textarea name="content" id="edit_blog_content" class="textarea textarea-bordered" rows="12" required></textarea>
                </div>

                <div class="modal-action">
                    <button type="button" onclick="document.getElementById('editBlogPostModal').close()" class="btn btn-outline">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <!-- Модальное окно для редактирования вакансии -->
    <dialog id="editVacancyModal" class="modal modal-lg">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Редактировать вакансию</h3>
            <form id="editVacancyForm">
                <input type="hidden" id="edit_vacancy_id" name="id" />
                <input type="hidden" id="edit_vacancy_display_order" name="display_order" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Заголовок *</span>
                        </label>
                        <input type="text" id="edit_vacancy_title" name="title" class="input input-bordered" required />
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Подзаголовок *</span>
                        </label>
                        <textarea id="edit_vacancy_subtitle" name="subtitle" class="textarea textarea-bordered" rows="10" required></textarea>
                    </div>
                </div>

                <div class="modal-action">
                    <button type="button" onclick="document.getElementById('editVacancyModal').close()" class="btn btn-outline">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
    
    <script>
        // Инициализация CKEditor для редактирования контента
        let editorInstance = null;
        let editEditorInstance = null;
        
        // Функции для проектов (должны быть глобальными для использования в onclick)
        window.editProject = function(project) {
            document.getElementById('edit_project_id').value = project.id;
            document.getElementById('edit_project_title').value = project.title;
            document.getElementById('edit_project_link').value = project.link;
            document.getElementById('edit_project_subtitle').value = project.subtitle || '';
            document.getElementById('edit_project_description').value = project.description;
            const whatDoneEl = document.getElementById('edit_project_what_done');
            if (whatDoneEl) whatDoneEl.value = project.what_done || '';
            const techEl = document.getElementById('edit_project_technologies');
            if (techEl) techEl.value = project.technologies || '';
            document.getElementById('edit_project_logo_path').value = project.logo_path;
            document.getElementById('edit_project_tags').value = project.tags;
            document.getElementById('edit_project_website').value = project.website || '';
            document.getElementById('edit_project_display_order').value = project.display_order;
            
            // Показываем текущий логотип
            const previewImg = document.getElementById('edit_logo_preview_img');
            if (project.logo_path) {
                previewImg.src = project.logo_path;
                previewImg.style.display = 'block';
            } else {
                previewImg.style.display = 'none';
            }
            
            document.getElementById('editProjectModal').showModal();
            
            // Инициализируем CKEditor после открытия модального окна
            setTimeout(function() {
                if (editProjectDescriptionEditor) {
                    editProjectDescriptionEditor.destroy();
                    editProjectDescriptionEditor = null;
                }
                if (document.getElementById('edit_project_description')) {
                    editProjectDescriptionEditor = CKEDITOR.replace('edit_project_description');
                    if (editProjectDescriptionEditor) {
                        editProjectDescriptionEditor.setData(project.description || '');
                    }
                }
            }, 100);
        };
        
        // Обработка загрузки логотипа при создании проекта
        const logoUpload = document.getElementById('logo_upload');
        if (logoUpload) {
            logoUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('logo', file);
                
                fetch('api/upload_logo.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('logo_path').value = data.path;
                        document.getElementById('logo_preview_img').src = data.path;
                        document.getElementById('logo_preview').classList.remove('hidden');
                    } else {
                        alert('Ошибка загрузки: ' + data.message);
                        e.target.value = '';
                    }
                })
                .catch(error => {
                    alert('Ошибка: ' + error);
                    e.target.value = '';
                });
            }
            });
        }
        
        // Обработка загрузки логотипа при редактировании проекта на отдельной странице
        const editPageLogoUpload = document.getElementById('edit_page_logo_upload');
        if (editPageLogoUpload) {
            editPageLogoUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('logo', file);
                
                fetch('api/upload_logo.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_page_logo_path').value = data.path;
                        document.getElementById('edit_page_logo_preview_img').src = data.path;
                    } else {
                        alert('Ошибка загрузки: ' + data.message);
                        e.target.value = '';
                    }
                })
                .catch(error => {
                    alert('Ошибка: ' + error);
                    e.target.value = '';
                });
            }
            });
        }
        
        // Обработка загрузки логотипа при редактировании проекта в модальном окне
        const editLogoUpload = document.getElementById('edit_logo_upload');
        if (editLogoUpload) {
            editLogoUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('logo', file);
                
                fetch('api/upload_logo.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_project_logo_path').value = data.path;
                        document.getElementById('edit_logo_preview_img').src = data.path;
                        document.getElementById('edit_logo_preview_img').style.display = 'block';
                    } else {
                        alert('Ошибка загрузки: ' + data.message);
                        e.target.value = '';
                    }
                })
                .catch(error => {
                    alert('Ошибка: ' + error);
                    e.target.value = '';
                });
            }
            });
        }
        
        const projectForm = document.getElementById('projectForm');
        if (projectForm) {
            projectForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const logoPath = document.getElementById('logo_path').value.trim();
            if (!logoPath) {
                alert('Пожалуйста, загрузите логотип');
                return;
            }
            
            // Убираем файл из формы, так как он уже загружен
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('title', document.querySelector('#projectForm input[name="title"]').value);
            formData.append('subtitle', document.querySelector('#projectForm input[name="subtitle"]')?.value || '');
            formData.append('description', projectDescriptionEditor ? projectDescriptionEditor.getData() : document.querySelector('#projectForm textarea[name="description"]').value);
            formData.append('what_done', document.querySelector('#projectForm textarea[name="what_done"]')?.value || '');
            formData.append('technologies', document.querySelector('#projectForm textarea[name="technologies"]')?.value || '');
            formData.append('link', document.querySelector('#projectForm input[name="link"]').value);
            formData.append('logo_path', logoPath);
            formData.append('tags', document.querySelector('#projectForm input[name="tags"]').value);
            formData.append('website', document.querySelector('#projectForm input[name="website"]')?.value || '');
            formData.append('display_order', document.querySelector('#projectForm input[name="display_order"]').value || '0');
            
            fetch('api/projects.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Проект успешно добавлен!');
                    location.reload();
                } else {
                    alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                alert('Ошибка: ' + error);
            });
        });
        }
        
        const editProjectForm = document.getElementById('editProjectForm');
        if (editProjectForm) {
            editProjectForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // При редактировании используем текущий логотип, если новый не загружен
            // Текущий путь уже установлен в скрытое поле при открытии формы редактирования
            let logoPath = document.getElementById('edit_project_logo_path').value.trim();
            if (!logoPath) {
                alert('Пожалуйста, загрузите логотип');
                return;
            }
            
            // Убираем файл из формы, так как он уже загружен
            const formData = new FormData();
            formData.append('action', 'edit');
            formData.append('id', document.getElementById('edit_project_id').value);
            formData.append('title', document.getElementById('edit_project_title').value);
            formData.append('description', editProjectDescriptionEditor ? editProjectDescriptionEditor.getData() : document.getElementById('edit_project_description').value);
            formData.append('what_done', document.getElementById('edit_project_what_done')?.value || '');
            formData.append('technologies', document.getElementById('edit_project_technologies')?.value || '');
            formData.append('link', document.getElementById('edit_project_link').value);
            formData.append('logo_path', logoPath);
            formData.append('tags', document.getElementById('edit_project_tags').value);
            formData.append('website', document.getElementById('edit_project_website').value || '');
            formData.append('subtitle', document.getElementById('edit_project_subtitle').value || '');
            formData.append('display_order', document.getElementById('edit_project_display_order').value || '0');
            
            fetch('api/projects.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Проект успешно обновлен!');
                    window.location.href = '?tab=projects';
                } else {
                    alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                alert('Ошибка: ' + error);
            });
        });
        }
        
        // Обработка формы редактирования проекта на отдельной странице
        const editProjectPageForm = document.getElementById('editProjectPageForm');
        if (editProjectPageForm) {
            editProjectPageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // При редактировании используем текущий логотип, если новый не загружен
            let logoPath = document.getElementById('edit_page_logo_path').value.trim();
            if (!logoPath) {
                alert('Пожалуйста, загрузите логотип');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'edit');
            formData.append('id', document.querySelector('#editProjectPageForm input[name="id"]').value);
            formData.append('title', document.querySelector('#editProjectPageForm input[name="title"]').value);
            formData.append('subtitle', document.querySelector('#editProjectPageForm input[name="subtitle"]')?.value || '');
            formData.append('description', editProjectDescriptionEditor ? editProjectDescriptionEditor.getData() : document.querySelector('#editProjectPageForm textarea[name="description"]').value);
            formData.append('what_done', document.querySelector('#editProjectPageForm textarea[name="what_done"]')?.value || '');
            formData.append('technologies', document.querySelector('#editProjectPageForm textarea[name="technologies"]')?.value || '');
            formData.append('link', document.querySelector('#editProjectPageForm input[name="link"]').value);
            formData.append('logo_path', logoPath);
            formData.append('tags', document.querySelector('#editProjectPageForm input[name="tags"]').value);
            formData.append('website', document.querySelector('#editProjectPageForm input[name="website"]')?.value || '');
            formData.append('display_order', document.querySelector('#editProjectPageForm input[name="display_order"]').value || '0');
            
            fetch('api/projects.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Проект успешно обновлен!');
                    window.location.href = '?tab=projects';
                } else {
                    alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                alert('Ошибка: ' + error);
            });
        });
        }
        
        // =========================
        // ГАЛЕРЕЯ ПРОЕКТА (с нуля)
        // =========================
        (function () {
            const modal = document.getElementById('galleryModal');
            const projectIdInput = document.getElementById('gallery_project_id');
            const fileInput = document.getElementById('gallery_image_upload');
            const list = document.getElementById('gallery_images_list');
            const progressWrap = document.getElementById('upload-progress');
            const progressBar = document.getElementById('upload-progress-bar');
            const statusEl = document.getElementById('upload-status');

            if (!modal || !projectIdInput || !fileInput || !list || !progressWrap || !progressBar || !statusEl) {
                // Если вкладка не "Проекты" или модалка не в DOM — просто не инициализируем галерею.
                return;
            }

            let currentProjectId = null;
            let isUploading = false;

            function setProgress(visible, value, text) {
                if (visible) progressWrap.classList.remove('hidden');
                else progressWrap.classList.add('hidden');
                if (typeof value === 'number') progressBar.value = value;
                if (typeof text === 'string') statusEl.textContent = text;
            }

            function openDialog() {
                try {
                    if (!modal.open && typeof modal.showModal === 'function') modal.showModal();
                    else modal.setAttribute('open', '');
                } catch (e) {
                    modal.setAttribute('open', '');
                }
            }

            async function apiJson(url, options) {
                const res = await fetch(url, options);
                const text = await res.text();
                let json;
                try { json = JSON.parse(text); } catch { json = null; }
                if (!res.ok) {
                    const msg = (json && json.message) ? json.message : text || `HTTP ${res.status}`;
                    throw new Error(msg);
                }
                if (!json) throw new Error('Некорректный ответ сервера');
                return json;
            }

            function renderEmpty() {
                list.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500">Изображений пока нет. Загрузите первое.</div>';
            }

            function renderImages(images) {
                if (!images || images.length === 0) return renderEmpty();
                list.innerHTML = images.map((img) => {
                    const safeSrc = String(img.image_path || '');
                    const safeId = String(img.id || '');
                    return `
                        <div class="relative group">
                            <img src="${safeSrc}" alt="Gallery image" class="w-full h-32 object-cover border border-gray-300 rounded" />
                            <button type="button" data-gallery-delete="${safeId}" class="absolute top-2 right-2 btn btn-sm btn-error opacity-0 group-hover:opacity-100 transition-opacity">✕</button>
                        </div>
                    `;
                }).join('');
            }

            async function loadImages() {
                if (!currentProjectId) return;
                try {
                    list.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500">Загрузка…</div>';
                    const data = await apiJson(`api/project_gallery.php?action=get&project_id=${encodeURIComponent(currentProjectId)}`);
                    if (!data.success) throw new Error(data.message || 'Не удалось загрузить галерею');
                    renderImages(data.images || []);
                } catch (e) {
                    list.innerHTML = `<div class="col-span-full text-center py-8 text-red-600">Ошибка: ${String(e.message || e)}</div>`;
                }
            }

            async function deleteImage(imageId) {
                if (!currentProjectId) return;
                if (!confirm('Удалить это изображение из галереи?')) return;
                try {
                    const fd = new FormData();
                    fd.append('action', 'delete');
                    fd.append('id', String(imageId));
                    const data = await apiJson('api/project_gallery.php', { method: 'POST', body: fd });
                    if (!data.success) throw new Error(data.message || 'Не удалось удалить');
                    await loadImages();
                } catch (e) {
                    alert('Ошибка удаления: ' + (e.message || e));
                }
            }

            async function uploadFiles(fileList) {
                if (!currentProjectId) return;
                const files = Array.from(fileList || []).filter(Boolean);
                if (files.length === 0) return;
                if (isUploading) return;
                isUploading = true;

                let ok = 0;
                let fail = 0;
                const total = files.length;
                setProgress(true, 0, `Загрузка 0 / ${total}`);

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    try {
                        const fdUp = new FormData();
                        fdUp.append('image', file);
                        const up = await apiJson('api/upload_gallery_image.php', { method: 'POST', body: fdUp });
                        if (!up.success) throw new Error(up.message || 'Ошибка загрузки файла');

                        const fdAdd = new FormData();
                        fdAdd.append('action', 'add');
                        fdAdd.append('project_id', String(currentProjectId));
                        fdAdd.append('image_path', String(up.path));
                        fdAdd.append('display_order', '0');
                        const added = await apiJson('api/project_gallery.php', { method: 'POST', body: fdAdd });
                        if (!added.success) throw new Error(added.message || 'Ошибка добавления в БД');

                        ok++;
                    } catch (e) {
                        fail++;
                    }

                    const pct = Math.round(((i + 1) / total) * 100);
                    setProgress(true, pct, `Загрузка ${i + 1} / ${total} (успешно: ${ok}, ошибок: ${fail})`);
                }

                await loadImages();
                setTimeout(() => setProgress(false, 0, ''), 400);
                fileInput.value = '';
                isUploading = false;

                if (fail > 0) {
                    alert(`Загрузка завершена. Успешно: ${ok}. Ошибок: ${fail}.`);
                }
            }

            // Открытие модалки
            document.addEventListener('click', (e) => {
                const openBtn = e.target.closest('.js-open-gallery');
                if (openBtn) {
                    e.preventDefault();
                    currentProjectId = openBtn.getAttribute('data-project-id');
                    projectIdInput.value = currentProjectId || '';
                    fileInput.value = '';
                    setProgress(false, 0, '');
                    openDialog();
                    loadImages();
                    return;
                }

                const delBtn = e.target.closest('[data-gallery-delete]');
                if (delBtn) {
                    e.preventDefault();
                    deleteImage(delBtn.getAttribute('data-gallery-delete'));
                    return;
                }

                const closeBtn = e.target.closest('[data-gallery-close]');
                if (closeBtn) {
                    e.preventDefault();
                    try { modal.close(); } catch { modal.removeAttribute('open'); }
                }
            });

            // Загрузка файлов
            fileInput.addEventListener('change', () => uploadFiles(fileInput.files));

            // Сброс при закрытии
            modal.addEventListener('close', () => {
                currentProjectId = null;
                isUploading = false;
                fileInput.value = '';
                setProgress(false, 0, '');
            });
        })();

        // Функции для страниц (должны быть глобальными)
        window.editPage = function(page) {
            document.getElementById('edit_page_id').value = page.id;
            document.getElementById('edit_page_slug').value = page.slug;
            document.getElementById('edit_page_title').value = page.title;
            document.getElementById('edit_page_meta_description').value = page.meta_description || '';
            
            // Загружаем полный контент страницы
            fetch(`api/pages.php?action=get&slug=${encodeURIComponent(page.slug)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Уничтожаем предыдущий редактор если есть
                        if (editEditorInstance) {
                            editEditorInstance.destroy();
                            editEditorInstance = null;
                        }
                        
                        document.getElementById('edit_page_content').value = data.page.content;
                        
                        // Открываем модальное окно
                        document.getElementById('editPageModal').showModal();
                        
                        // Инициализируем редактор после открытия модального окна
                        setTimeout(function() {
                            editEditorInstance = CKEDITOR.replace('edit_page_content');
                        }, 100);
                    } else {
                        alert('Ошибка загрузки страницы: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Ошибка: ' + error);
                });
        };
        
        window.deletePage = function(id) {
            if (!confirm('Вы уверены, что хотите удалить эту страницу?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            fetch('api/pages.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Страница успешно удалена!');
                    location.reload();
                } else {
                    alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                alert('Ошибка: ' + error);
            });
        };
        
        const pageForm = document.getElementById('pageForm');
        if (pageForm) {
            pageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (editorInstance) {
                editorInstance.updateElement();
            }
            
            const formData = new FormData(this);
            
            fetch('api/pages.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Страница успешно создана!');
                    location.reload();
                } else {
                    alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                alert('Ошибка: ' + error);
            });
        });
        }
        
        const editPageForm = document.getElementById('editPageForm');
        if (editPageForm) {
            editPageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (editEditorInstance) {
                editEditorInstance.updateElement();
            }
            
            const formData = new FormData(this);
            
            fetch('api/pages.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Страница успешно обновлена!');
                    location.reload();
                } else {
                    alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                alert('Ошибка: ' + error);
            });
        });

        }

        // =========================
        // ВАКАНСИИ (с нуля)
        // =========================
        const vacancyForm = document.getElementById('vacancyForm');
        if (vacancyForm) {
            vacancyForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(vacancyForm);
                formData.append('action', 'add');

                try {
                    const res = await fetch('api/vacancies.php', { method: 'POST', body: formData });
                    const data = await res.json();
                    if (data.success) {
                        alert('Вакансия добавлена!');
                        location.reload();
                    } else {
                        alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                    }
                } catch (err) {
                    alert('Ошибка: ' + err);
                }
            });
        }

        // Редактирование вакансии
        window.editVacancy = function(vacancy) {
            document.getElementById('edit_vacancy_id').value = vacancy.id;
            document.getElementById('edit_vacancy_title').value = vacancy.title || '';
            document.getElementById('edit_vacancy_subtitle').value = vacancy.subtitle || '';
            document.getElementById('edit_vacancy_display_order').value = vacancy.display_order || 0;
            document.getElementById('editVacancyModal').showModal();
        };

        const editVacancyForm = document.getElementById('editVacancyForm');
        if (editVacancyForm) {
            editVacancyForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const fd = new FormData(editVacancyForm);
                fd.append('action', 'edit');
                try {
                    const res = await fetch('api/vacancies.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.success) {
                        alert('Вакансия обновлена!');
                        location.reload();
                    } else {
                        alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                    }
                } catch (err) {
                    alert('Ошибка: ' + err);
                }
            });
        }

        document.addEventListener('click', async function(e) {
            const btn = e.target.closest('[data-vacancy-delete]');
            if (!btn) return;
            e.preventDefault();

            const id = btn.getAttribute('data-vacancy-delete');
            if (!id) return;
            if (!confirm('Удалить эту вакансию?')) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            try {
                const res = await fetch('api/vacancies.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                }
            } catch (err) {
                alert('Ошибка: ' + err);
            }
        });

        // =========================
        // БЛОГ (с нуля)
        // =========================
        const blogImageUpload = document.getElementById('blog_image_upload');
        if (blogImageUpload) {
            blogImageUpload.addEventListener('change', async function(e) {
                const file = e.target.files && e.target.files[0];
                if (!file) return;

                const fd = new FormData();
                fd.append('image', file);

                try {
                    const res = await fetch('api/upload_blog_image.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.success) {
                        const pathInput = document.getElementById('blog_image_path');
                        if (pathInput) pathInput.value = data.path;

                        const previewWrap = document.getElementById('blog_image_preview');
                        const previewImg = document.getElementById('blog_image_preview_img');
                        if (previewWrap && previewImg) {
                            previewImg.src = data.path;
                            previewWrap.classList.remove('hidden');
                        }
                    } else {
                        alert('Ошибка загрузки превью: ' + (data.message || 'Неизвестная ошибка'));
                        e.target.value = '';
                    }
                } catch (err) {
                    alert('Ошибка: ' + err);
                    e.target.value = '';
                }
            });
        }

        const blogPostForm = document.getElementById('blogPostForm');
        if (blogPostForm) {
            blogPostForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const imagePath = document.getElementById('blog_image_path')?.value?.trim() || '';
                if (!imagePath) {
                    alert('Загрузите превью изображение');
                    return;
                }
                const contentVal = document.getElementById('blog_content')?.value?.trim() || '';
                if (!contentVal) {
                    alert('Заполните контент статьи');
                    return;
                }

                const fd = new FormData(blogPostForm);
                fd.append('action', 'add');
                fd.set('image_path', imagePath);

                try {
                    const res = await fetch('api/blog_posts.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.success) {
                        alert('Статья добавлена!');
                        location.reload();
                    } else {
                        alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                    }
                } catch (err) {
                    alert('Ошибка: ' + err);
                }
            });
        }

        // Редактирование статьи
        window.editBlogPost = function(post) {
            document.getElementById('edit_blog_id').value = post.id;
            document.getElementById('edit_blog_title').value = post.title || '';
            document.getElementById('edit_blog_subtitle').value = post.subtitle || '';
            document.getElementById('edit_blog_display_order').value = post.display_order || 0;
            document.getElementById('edit_blog_content').value = post.content || '';
            document.getElementById('edit_blog_image_path').value = post.image_path || '';
            const img = document.getElementById('edit_blog_image_preview_img');
            if (img) img.src = post.image_path || '';
            document.getElementById('editBlogPostModal').showModal();
        };

        const editBlogImageUpload = document.getElementById('edit_blog_image_upload');
        if (editBlogImageUpload) {
            editBlogImageUpload.addEventListener('change', async function(e) {
                const file = e.target.files && e.target.files[0];
                if (!file) return;

                const fd = new FormData();
                fd.append('image', file);

                try {
                    const res = await fetch('api/upload_blog_image.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.success) {
                        document.getElementById('edit_blog_image_path').value = data.path;
                        const img = document.getElementById('edit_blog_image_preview_img');
                        if (img) img.src = data.path;
                    } else {
                        alert('Ошибка загрузки превью: ' + (data.message || 'Неизвестная ошибка'));
                        e.target.value = '';
                    }
                } catch (err) {
                    alert('Ошибка: ' + err);
                    e.target.value = '';
                }
            });
        }

        const editBlogPostForm = document.getElementById('editBlogPostForm');
        if (editBlogPostForm) {
            editBlogPostForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const imagePath = document.getElementById('edit_blog_image_path')?.value?.trim() || '';
                if (!imagePath) {
                    alert('Загрузите превью изображение');
                    return;
                }
                const contentVal = document.getElementById('edit_blog_content')?.value?.trim() || '';
                if (!contentVal) {
                    alert('Заполните контент статьи');
                    return;
                }

                const fd = new FormData(editBlogPostForm);
                fd.append('action', 'edit');
                fd.set('image_path', imagePath);
                fd.set('id', document.getElementById('edit_blog_id').value);

                try {
                    const res = await fetch('api/blog_posts.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.success) {
                        alert('Статья обновлена!');
                        location.reload();
                    } else {
                        alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                    }
                } catch (err) {
                    alert('Ошибка: ' + err);
                }
            });
        }

        document.addEventListener('click', async function(e) {
            const btn = e.target.closest('[data-blog-delete]');
            if (!btn) return;
            e.preventDefault();

            const id = btn.getAttribute('data-blog-delete');
            if (!id) return;
            if (!confirm('Удалить эту статью?')) return;

            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);

            try {
                const res = await fetch('api/blog_posts.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                }
            } catch (err) {
                alert('Ошибка: ' + err);
            }
        });
        
        // Инициализация редактора при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('page_content') && document.getElementById('page_content').offsetParent !== null) {
                editorInstance = CKEDITOR.replace('page_content');
            }
            
            // Инициализация CKEditor для поля "Описание" в форме добавления проекта
            if (document.getElementById('project_description') && document.getElementById('project_description').offsetParent !== null) {
                projectDescriptionEditor = CKEDITOR.replace('project_description');
            }
            
            // Инициализация CKEditor для поля "Описание" в форме редактирования проекта на отдельной странице
            if (document.getElementById('edit_project_description_page') && document.getElementById('edit_project_description_page').offsetParent !== null) {
                editProjectDescriptionEditor = CKEDITOR.replace('edit_project_description_page');
            }
        });
        
        // При переходе между разделами страница перезагружается (?tab=...),
        // поэтому отдельная инициализация по клику больше не нужна.
    </script>
</body>
</html>
