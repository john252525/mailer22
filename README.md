```
composer require phpmailer/phpmailer

POST /mailer22/send
{
    "to":"to@email.com",
    "subject":"subject",
    "body":"body",
    "from_email":"from@email.com",
    "token":"config.token"
}

or

POST /mailer22/send
{
    "to":"to@email.com",
    "subject":"subject",
    "body":"body",
    "smtp_host":"smtp.example.com",
    "smtp_username":"user@example.com",
    "smtp_password":"password"
}

Additional parameters:
from_email
from_name
is_html
alt_body
smtp_port
smtp_secure
smtp_debug
charset
```
