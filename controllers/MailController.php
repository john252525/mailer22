<?php

// Example:
//$mailer = new MailController();
//$result = $mailer->send($input);

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailController
{
    private $config;
    private $mailer;

    public function __construct(array $defaultConfig = [])
    {
        $this->config = array_merge([
            'smtp_host' => '',  // smtp.example.com
            'smtp_username' => '',  // user@example.com
            'smtp_password' => '',  // password
            'smtp_port' => 587,
            'smtp_secure' => 'tls',
            'smtp_debug' => 0,
            'from_email' => '',  // user@example.com
            'from_name' => '',  // Mail Sender
            'charset' => 'UTF-8'
        ], $defaultConfig);

        $this->mailer = new PHPMailer(true);
    }

    public function send(array $params): array
    {
        try {
            $this->config = array_merge($this->config, $params);
            
            $this->enrichParams($this->config);
            $this->validateParams($this->config);
            $this->configureMailer($this->config);
            $this->prepareMessage($this->config);

            $this->mailer->send();
            return ['status' => 'success', 'message' => 'Email sent successfully'];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Message could not be sent',
                'error' => trim($e->getMessage() . ' ' . $this->mailer->ErrorInfo),
            ];
        }
    }

    private function enrichParams(array $params): void
    {
        // Обновляем конфиг переданными параметрами
        foreach ($this->config as $key => $value) {
            if (isset($params[$key])) {
                $this->config[$key] = $params[$key];
            }
        }

        if(!empty($this->config['smtp_username'])){
            if(empty($this->config['from_email'])) $this->config['from_email'] = $this->config['smtp_username'];
            if(empty($this->config['from_name']))  $this->config['from_name']  = $this->config['from_email'];
        }
        elseif(!empty($this->config['from_email'])) {
            if(empty($this->config['from_name']))  $this->config['from_name'] = $this->config['from_email'];

            if(!empty($this->config['token']) && $this->config['token'] == $this->config['config']['mail'][$this->config['from_email']]['token']){
                $this->config = array_merge($this->config, $this->config['config']['mail'][$this->config['from_email']]);
            }
        }
    }

    private function validateParams(array $params): void
    {
        $required = ['to', 'subject', 'body', 'smtp_host', 'smtp_username', 'smtp_password', 'from_email', 'from_name'];
        foreach ($required as $field) {
            if (empty($params[$field])) {
                throw new Exception("Missing required parameter: $field");
            }
        }
    }

    private function configureMailer(array $params): void
    {
        // Настройка SMTP
        $this->mailer->SMTPDebug = $this->config['smtp_debug'];
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['smtp_host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['smtp_username'];
        $this->mailer->Password = $this->config['smtp_password'];
        $this->mailer->SMTPSecure = $this->config['smtp_secure'];
        $this->mailer->Port = $this->config['smtp_port'];
        
        // Настройки кодировки
        $this->mailer->CharSet = $this->config['charset'];
        $this->mailer->Encoding = 'base64';
    }

    private function prepareMessage(array $params): void
    {
        $this->mailer->setFrom(
            $this->config['from_email'],
            $this->config['from_name']
        );
        
        $this->mailer->addAddress($params['to']);
        
        // Кодировка темы
        $this->mailer->Subject = $this->encodeSubject($params['subject']);
        
        // Тело письма
        $this->mailer->isHTML($params['is_html'] ?? false);
        $this->mailer->Body = $params['body'];
        
        // Альтернативное текстовое тело
        if (isset($params['alt_body'])) {
            $this->mailer->AltBody = $params['alt_body'];
        }
    }

    private function encodeSubject(string $subject): string
    {
        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }
}
