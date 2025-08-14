<?php
$config = [
    'smtp_host' => 'smtp.example.com',
    'smtp_username' => 'user@example.com',
    'smtp_password' => 'password',
    'smtp_port' => 587,
    'smtp_secure' => 'tls',
    'smtp_debug' => 2 // Уровень отладки (0-4)
];

// Получаем данные
$input = [];
if (php_sapi_name() === 'cli') {
    parse_str(implode('&', array_slice($argv, 1)), $input);
} else {
    $input = array_merge($_GET, $_POST);
    $json = json_decode(file_get_contents('php://input'), true);
    if ($json) $input = array_merge($input, $json);
}

// Обновляем конфиг
foreach ($config as $key => $value) {
    if (isset($input[$key])) {
        $config[$key] = $input[$key];
    }
}

// Проверяем обязательные поля
$required = ['to', 'subject', 'body'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        die(json_encode(['status' => 'error', 'message' => "Missing parameter: $field"]));
    }
}

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Серверные настройки
    $mail->SMTPDebug = $config['smtp_debug'];
    $mail->isSMTP();
    $mail->Host = $config['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_username'];
    $mail->Password = $config['smtp_password'];
    $mail->SMTPSecure = $config['smtp_secure'];
    $mail->Port = $config['smtp_port'];
    
    // Настройки кодировки
    $mail->CharSet = 'UTF-8'; // Устанавливаем кодировку
    $mail->Encoding = 'base64'; // Кодировка заголовков
    
    // Дополнительные настройки
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    // Получатели
    $mail->setFrom($config['smtp_username'], 'Имя отправителя'); // Можно указать имя в UTF-8
    $mail->addAddress($input['to']);

    // Содержание письма
    $mail->isHTML(false);
    $mail->Subject = '=?UTF-8?B?'.base64_encode($input['subject']).'?='; // Кодировка темы
    $mail->Body = $input['body'];

    $mail->send();
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
    ]);
}
