<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', dirname(__DIR__));
define('APP_CONTROLLERS', BASE_PATH . '/controllers');
define('APP_MODELS', BASE_PATH . '/models');
define('APP_VIEWS', BASE_PATH . '/View');
define('APP_CONFIG', BASE_PATH . '/config');

spl_autoload_register(function ($class) {
    $paths = [APP_CONTROLLERS, APP_MODELS, APP_CONFIG];
    foreach ($paths as $p) {
        $file = $p . '/' . $class . '.php';
        if (file_exists($file)) { require_once $file; return; }
        $fileLower = $p . '/' . strtolower($class) . '.php';
        if (file_exists($fileLower)) { require_once $fileLower; return; }
    }
});

function render(string $view, array $data = []): void {
    $viewFile = APP_VIEWS . '/' . $view . '.phtml';
    if (!file_exists($viewFile)) {
        http_response_code(500);
        echo "<h1>View não encontrada: {$view}.phtml</h1>";
        echo "<p>Procurado em: {$viewFile}</p>";
        exit;
    }
    extract($data, EXTR_SKIP);
    include $viewFile;
}

/* ---------- Router ---------- */

// Pega a URL via query string ou PATH_INFO
if (isset($_GET['url'])) {
    $uri = $_GET['url'];
} else {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $uri = parse_url($requestUri, PHP_URL_PATH) ?? '/';
    
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = str_replace('\\', '/', dirname($scriptName));
    $scriptDir = rtrim($scriptDir, '/');
    
    define('BASE_URL', $scriptDir);
    
    if ($scriptDir !== '' && $scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
        $uri = substr($uri, strlen($scriptDir));
    }
}

$uri = '/' . trim($uri, '/');
$uri = trim($uri, '/');


// DEBUG - teste do negocio que não aparece url 
/*
echo "<pre>";
echo "REQUEST_URI: $requestUri\n";
echo "SCRIPT_NAME: $scriptName\n";
echo "SCRIPT_DIR: $scriptDir\n";
echo "URI Final: $uri\n";
echo "BASE_URL: " . BASE_URL . "\n";
echo "</pre>";
exit;
*/


// Quebra em segmentos
$segments = $uri === '' ? [] : explode('/', $uri);

// Padrões
$controller = $segments[0] ?? 'home';
$action     = $segments[1] ?? 'index';
$params     = array_slice($segments, 2);




// ... resto do código continua igual
// Nome da classe do controller
$controllerClass = ucfirst($controller) . 'Controller';


$method = preg_replace_callback('/-([a-z])/', function ($m) {
    return strtoupper($m[1]);
}, $action);

// Carrega e executa
if (!class_exists($controllerClass)) {
    http_response_code(404);
    echo "<h1>404</h1><p>Controller <strong>{$controllerClass}</strong> não encontrado.</p>";
    exit;
}

$controllerObj = new $controllerClass();

if (!method_exists($controllerObj, $method)) {
    http_response_code(404);
    echo "<h1>404</h1><p>Ação <strong>{$controllerClass}::{$method}()</strong> não encontrada.</p>";
    exit;
}

call_user_func_array([$controllerObj, $method], $params);
