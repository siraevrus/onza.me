<?php
/**
 * API для загрузки превью изображений статей блога
 */
require_once __DIR__ . '/../config/auth.php';
requireAuth();

header('Content-Type: application/json');

$uploadDir = __DIR__ . '/../assets/blog/';

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Не удалось создать директорию для загрузки']);
        exit;
    }
}

if (!is_writable($uploadDir)) {
    echo json_encode(['success' => false, 'message' => 'Директория не доступна для записи']);
    exit;
}

$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
    exit;
}

if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'Файл не был загружен. Поле должно называться "image"']);
    exit;
}

$file = $_FILES['image'];
if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Ошибка загрузки файла (код: ' . (int)$file['error'] . ')']);
    exit;
}

if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
    echo json_encode(['success' => false, 'message' => 'Файл не был загружен корректно']);
    exit;
}

$fileName = $file['name'] ?? 'image';
$fileTmpName = $file['tmp_name'];
$fileSize = (int)($file['size'] ?? 0);
$fileType = $file['type'] ?? '';

$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
if (!in_array($fileExtension, $allowedExtensions, true) || !in_array($fileType, $allowedTypes, true)) {
    echo json_encode(['success' => false, 'message' => 'Недопустимый тип файла. Разрешены: JPG, PNG, GIF, WEBP']);
    exit;
}

// 5MB
if ($fileSize > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Файл слишком большой. Максимум 5MB']);
    exit;
}

$baseName = pathinfo($fileName, PATHINFO_FILENAME);
$baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
$newFileName = 'blog_' . $baseName . '_' . time() . '.' . $fileExtension;
$targetPath = $uploadDir . $newFileName;

if (!move_uploaded_file($fileTmpName, $targetPath)) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении файла']);
    exit;
}

$relativePath = 'assets/blog/' . $newFileName;
echo json_encode(['success' => true, 'path' => $relativePath]);

