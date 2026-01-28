<?php
/**
 * API для загрузки изображений галереи проектов
 */
require_once __DIR__ . '/../config/auth.php';
requireAuth();

header('Content-Type: application/json');

// Директория для сохранения изображений галереи
$uploadDir = __DIR__ . '/../assets/projects/';

// Проверяем, что директория существует
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Не удалось создать директорию для загрузки']);
        exit;
    }
}

// Проверяем права на запись
if (!is_writable($uploadDir)) {
    echo json_encode(['success' => false, 'message' => 'Директория не доступна для записи']);
    exit;
}

// Разрешенные типы файлов
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
    exit;
}

if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'Файл не был загружен. Проверьте, что поле называется "image"']);
    exit;
}

$fileError = $_FILES['image']['error'];
if ($fileError !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'Файл превышает максимальный размер, установленный в php.ini',
        UPLOAD_ERR_FORM_SIZE => 'Файл превышает максимальный размер формы',
        UPLOAD_ERR_PARTIAL => 'Файл был загружен частично',
        UPLOAD_ERR_NO_FILE => 'Файл не был загружен',
        UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка',
        UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск',
        UPLOAD_ERR_EXTENSION => 'Загрузка остановлена расширением PHP'
    ];
    $errorMsg = $errorMessages[$fileError] ?? 'Неизвестная ошибка загрузки (код: ' . $fileError . ')';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

$file = $_FILES['image'];

// Проверяем, что файл действительно загружен
if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
    echo json_encode(['success' => false, 'message' => 'Файл не был загружен корректно']);
    exit;
}

$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileType = $file['type'];

// Проверка типа файла
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
if (!in_array($fileExtension, $allowedExtensions) || !in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Недопустимый тип файла. Разрешены: JPG, PNG, GIF, SVG, WEBP']);
    exit;
}

// Проверка размера файла (максимум 10MB)
if ($fileSize > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Файл слишком большой. Максимум 10MB']);
    exit;
}

// Генерируем уникальное имя файла
$baseName = pathinfo($fileName, PATHINFO_FILENAME);
$baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
$newFileName = 'gallery_' . $baseName . '_' . time() . '.' . $fileExtension;
$targetPath = $uploadDir . $newFileName;

// Перемещаем файл
if (!move_uploaded_file($fileTmpName, $targetPath)) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении файла']);
    exit;
}

// Возвращаем путь к файлу относительно корня сайта
$relativePath = 'assets/projects/' . $newFileName;

echo json_encode([
    'success' => true,
    'message' => 'Изображение успешно загружено',
    'path' => $relativePath,
    'url' => '/' . $relativePath
]);
?>
