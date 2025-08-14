<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


$config = require_once 'config.php';

$input = parseInput();

$request = array_merge($input, ['config'=>$config]);

$routes = [
    // '/' => 'HomeController@index',
    // '/about' => 'AboutController@index',
    // '/contact' => 'ContactController@submit',
    '/mailer22/send' => 'MailController@send',
];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (array_key_exists($uri, $routes)) {
    list($controller, $method) = explode('@', $routes[$uri]);
    require_once "controllers/$controller.php";
    $result = (new $controller())->$method($request);
    echo json_encode($result);
} else {
    http_response_code(404);
    echo '404 Not Found';
}








/*
function parseInput() {  // previous version
    // Получаем данные
    $input = [];
    if (php_sapi_name() === 'cli') {
        parse_str(implode('&', array_slice($argv, 1)), $input);
    } else {
        $input = array_merge($_GET, $_POST);
        $json = json_decode(file_get_contents('php://input'), true);
        if ($json) $input = array_merge($input, $json);
    }
    return $input;
}
*/

function parseInput() {
    $input = [
        'get' => $_GET,
        'post' => $_POST,
        'json' => json_decode(file_get_contents('php://input'), true) ?? [],
        'argv' => parseCliArgs()
    ];
    
    return array_merge($input['get'], $input['post'], $input['json'], $input['argv']);
}

function parseCliArgs() {
    global $argv;
    $args = [];
    if (isset($argv)) {
        foreach ($argv as $arg) {
            if (strpos($arg, '=') !== false) {
                list($key, $value) = explode('=', $arg);
                $args[$key] = $value;
            }
        }
    }
    return $args;
}
