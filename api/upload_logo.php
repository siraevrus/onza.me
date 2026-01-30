<?php
/**
 * API для загрузки логотипов проектов
 */
require_once __DIR__ . '/../config/auth.php';
requireAuth();
requireCsrf();

header('Content-Type: application/json');

// Директория для сохранения логотипов
$uploadDir = __DIR__ . '/../assets/logos/';

// Проверяем, что директория существует
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Разрешенные типы файлов (SVG запрещен из-за возможности XSS атак)
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
    exit;
}

if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Ошибка загрузки файла']);
    exit;
}

$file = $_FILES['logo'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileType = $file['type'];

// Проверка типа файла
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
if (!in_array($fileExtension, $allowedExtensions) || !in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Недопустимый тип файла. Разрешены: JPG, PNG, GIF, WEBP']);
    exit;
}

// Проверка размера файла (максимум 5MB)
if ($fileSize > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Файл слишком большой. Максимум 5MB']);
    exit;
}

// Генерируем уникальное имя файла
$baseName = pathinfo($fileName, PATHINFO_FILENAME);
$baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName); // Очищаем имя от спецсимволов
$newFileName = $baseName . '_' . time() . '.' . $fileExtension;
$targetPath = $uploadDir . $newFileName;

// Перемещаем файл
if (!move_uploaded_file($fileTmpName, $targetPath)) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении файла']);
    exit;
}

// Возвращаем путь к файлу относительно корня сайта
$relativePath = 'assets/logos/' . $newFileName;

echo json_encode([
    'success' => true,
    'message' => 'Логотип успешно загружен',
    'path' => $relativePath,
    'url' => '/' . $relativePath
]);
?>
