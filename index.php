<?php
$config = [
    'smtp_host' => 'smtp.example.com',
    'smtp_username' => 'user@example.com',
    'smtp_password' => 'password',
    'smtp_port' => 587,
    'smtp_secure' => 'tls'
];

// Получаем входные данные из разных источников
$input = [];
if (php_sapi_name() === 'cli') {
    parse_str(implode('&', array_slice($argv, 1)), $input);
} else {
    $input = array_merge($_GET, $_POST);
    if (!empty(file_get_contents('php://input'))) {
        $json = json_decode(file_get_contents('php://input'), true);
        if ($json) $input = array_merge($input, $json);
    }
}

// Обновляем конфиг переданными параметрами
foreach ($config as $key => $value) {
    if (isset($input[$key])) {
        $config[$key] = $input[$key];
    }
}

// Проверяем обязательные параметры
$to = $input['to'] ?? die("Укажите получателя (параметр 'to')\n");
$subject = $input['subject'] ?? die("Укажите тему (параметр 'subject')\n");
$body = $input['body'] ?? die("Укажите текст письма (параметр 'body')\n");

// Отправка
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = $config['smtp_host'];
$mail->SMTPAuth = true;
$mail->Username = $config['smtp_username'];
$mail->Password = $config['smtp_password'];
$mail->Port = $config['smtp_port']; 
$mail->SMTPSecure = $config['smtp_secure'];

$mail->setFrom($config['smtp_username']);
$mail->addAddress($to);
$mail->Subject = $subject;
$mail->Body = $body;

if ($mail->send()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $mail->ErrorInfo]);
}

