<?php
/**
 * Конфигурация базы данных SQLite
 */

// Путь к файлу базы данных
define('DB_PATH', __DIR__ . '/../database.db');

// Создаем подключение к базе данных
function getDB() {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $db;
    } catch (PDOException $e) {
        // Логируем ошибку (если есть возможность)
        error_log('Database connection error: ' . $e->getMessage());
        // Показываем пользователю общее сообщение без технических деталей
        die('Произошла ошибка при подключении к базе данных. Пожалуйста, попробуйте позже.');
    }
}

// Инициализация базы данных (создание таблиц, если их нет)
function initDB() {
    $db = getDB();
    
    // Таблица пользователей
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Таблица проектов
    $db->exec("CREATE TABLE IF NOT EXISTS projects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        what_done TEXT DEFAULT '',
        technologies TEXT DEFAULT '',
        link TEXT NOT NULL,
        logo_path TEXT NOT NULL,
        tags TEXT NOT NULL,
        display_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Миграция: добавляем недостающие колонки в projects (если база уже создана)
    $columns = $db->query("PRAGMA table_info(projects)")->fetchAll();
    $existing = [];
    foreach ($columns as $col) {
        $existing[$col['name']] = true;
    }
    if (!isset($existing['what_done'])) {
        $db->exec("ALTER TABLE projects ADD COLUMN what_done TEXT DEFAULT ''");
    }
    if (!isset($existing['technologies'])) {
        $db->exec("ALTER TABLE projects ADD COLUMN technologies TEXT DEFAULT ''");
    }
    if (!isset($existing['website'])) {
        $db->exec("ALTER TABLE projects ADD COLUMN website TEXT DEFAULT ''");
    }
    if (!isset($existing['subtitle'])) {
        $db->exec("ALTER TABLE projects ADD COLUMN subtitle TEXT DEFAULT ''");
    }

    // Миграция: blog_posts (если таблица уже есть, но колонок не хватает)
    $blogTables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='blog_posts'")->fetchAll();
    if (!empty($blogTables)) {
        $blogCols = $db->query("PRAGMA table_info(blog_posts)")->fetchAll();
        $blogExisting = [];
        foreach ($blogCols as $col) {
            $blogExisting[$col['name']] = true;
        }
        if (!isset($blogExisting['content'])) {
            $db->exec("ALTER TABLE blog_posts ADD COLUMN content TEXT NOT NULL DEFAULT ''");
        }
        if (!isset($blogExisting['image_path'])) {
            $db->exec("ALTER TABLE blog_posts ADD COLUMN image_path TEXT NOT NULL DEFAULT ''");
        }
        if (!isset($blogExisting['display_order'])) {
            $db->exec("ALTER TABLE blog_posts ADD COLUMN display_order INTEGER DEFAULT 0");
        }
    }
    
    // Таблица страниц для CMS
    $db->exec("CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT UNIQUE NOT NULL,
        title TEXT NOT NULL,
        meta_description TEXT,
        content TEXT NOT NULL,
        is_active INTEGER DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Таблица изображений галереи проектов
    $db->exec("CREATE TABLE IF NOT EXISTS project_gallery (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        project_id INTEGER NOT NULL,
        image_path TEXT NOT NULL,
        display_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
    )");

    // Таблица вакансий
    $db->exec("CREATE TABLE IF NOT EXISTS vacancies (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        subtitle TEXT NOT NULL,
        display_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Таблица статей блога
    $db->exec("CREATE TABLE IF NOT EXISTS blog_posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        subtitle TEXT NOT NULL,
        image_path TEXT NOT NULL,
        content TEXT NOT NULL DEFAULT '',
        display_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Таблица услуг
    $db->exec("CREATE TABLE IF NOT EXISTS services (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        subtitle TEXT NOT NULL,
        detail_subtitle TEXT DEFAULT '',
        slug TEXT UNIQUE NOT NULL,
        display_order INTEGER DEFAULT 0,
        is_active INTEGER DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Таблица блоков услуг
    $db->exec("CREATE TABLE IF NOT EXISTS service_blocks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        service_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        content TEXT NOT NULL,
        has_background INTEGER DEFAULT 0,
        display_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
    )");
    
    // Создаем администратора по умолчанию, если его нет
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    if ($stmt->fetchColumn() == 0) {
        // Пароль по умолчанию: admin123 (хэшированный)
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute(['admin', $passwordHash]);
    }
}

// Инициализируем БД при подключении
initDB();
?>
