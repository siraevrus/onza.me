<?php
declare(strict_types=1);

// Simple mail handler for contacts form
// Expects POST fields: name, email, company (optional), budget (optional), message, agree (on), attachment (optional file)

header('Content-Type: application/json; charset=UTF-8');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не поддерживается']);
    exit;
}

// Basic sanitization
$name    = trim((string)($_POST['name'] ?? ''));
$email   = trim((string)($_POST['email'] ?? ''));
$company = trim((string)($_POST['company'] ?? ''));
$budget  = trim((string)($_POST['budget'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));
$agree   = isset($_POST['agree']);

if ($name === '' || $email === '' || $message === '' || !$agree) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Заполните обязательные поля и подтвердите согласие']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Некорректный email']);
    exit;
}

// Build email content
$to = 'ruslan@siraev.ru';
$subject = 'Новая заявка с сайта ONZA.ME';

$textBody = "Имя: {$name}\n" .
            "Email: {$email}\n" .
            ( $company !== '' ? "Компания: {$company}\n" : '' ) .
            ( $budget !== '' ? "Бюджет: {$budget}\n" : '' ) .
            "\nСообщение:\n{$message}\n";

$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'From: ONZA.ME <no-reply@onza.me>';
$headers[] = 'Reply-To: ' . $email;

$hasFile = isset($_FILES['attachment']) && is_array($_FILES['attachment']) && (int)$_FILES['attachment']['error'] === UPLOAD_ERR_OK;

if ($hasFile) {
    // Attachment handling
    $fileTmp  = (string)$_FILES['attachment']['tmp_name'];
    $fileName = (string)$_FILES['attachment']['name'];
    $fileType = (string)($_FILES['attachment']['type'] ?: 'application/octet-stream');
    $fileSize = (int)$_FILES['attachment']['size'];

    // Limit ~10MB
    if ($fileSize > 10 * 1024 * 1024) {
        http_response_code(413);
        echo json_encode(['success' => false, 'error' => 'Файл слишком большой (макс. 10MB)']);
        exit;
    }

    $boundary = '==Multipart_Boundary_x' . bin2hex(random_bytes(8)) . 'x';
    $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

    $messageBody  = "--{$boundary}\r\n";
    $messageBody .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $messageBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $messageBody .= $textBody . "\r\n";

    $fileContent = chunk_split(base64_encode((string)file_get_contents($fileTmp)));
    $messageBody .= "--{$boundary}\r\n";
    $messageBody .= 'Content-Type: ' . $fileType . '; name="' . addslashes($fileName) . ""\r\n";
    $messageBody .= 'Content-Disposition: attachment; filename="' . addslashes($fileName) . ""\r\n";
    $messageBody .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $messageBody .= $fileContent . "\r\n";
    $messageBody .= "--{$boundary}--";

    $finalBody = $messageBody;
} else {
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $finalBody = $textBody;
}

// Encode subject for UTF-8
if (function_exists('mb_encode_mimeheader')) {
    $subject = mb_encode_mimeheader($subject, 'UTF-8');
}

$result = @mail($to, $subject, $finalBody, implode("\r\n", $headers));

if ($result) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Не удалось отправить письмо на сервере']);
}


