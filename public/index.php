<?php
// public/index.php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Caminhos base
define('BASE_PATH', dirname(__DIR__)); // pasta raiz do projeto
define('APP_CONTROLLERS', BASE_PATH . '/controllers');
define('APP_MODELS', BASE_PATH . '/models');
define('APP_VIEWS', BASE_PATH . '/views');
define('APP_CONFIG', BASE_PATH . '/config');

// *** MUITO IMPORTANTE ***
// Tem que bater com o caminho que aparece NO NAVEGADOR até a pasta public
define('BASE_URL', '/ecommerce-auto-pecas/public');

// Autoload simples de controllers, models e config
spl_autoload_register(function ($class) {
    $paths = [APP_CONTROLLERS, APP_MODELS, APP_CONFIG];
    foreach ($paths as $p) {
        $file = $p . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
        $fileLower = $p . '/' . strtolower($class) . '.php';
        if (file_exists($fileLower)) {
            require_once $fileLower;
            return;
        }
    }
});

// Função pra renderizar views .phtml
function render(string $view, array $data = []): void {
    extract($data);
    $viewFile = APP_VIEWS . '/' . $view . '.phtml';

    if (!file_exists($viewFile)) {
        http_response_code(500);
        echo "<h1>View não encontrada: {$view}.phtml</h1>";
        return;
    }

    require $viewFile;
}

// ------------- ROTEAMENTO -------------
// URL vem do .htaccess como ?url=controller/acao/param1/param2...
$url = $_GET['url'] ?? 'home/index';
$url = trim($url, '/');

$partes = explode('/', $url);

$controllerName = ucfirst($partes[0]) . 'Controller';
$actionName = $partes[1] ?? 'index';
$params = array_slice($partes, 2);

// Caminho do arquivo do controller
$controllerFile = APP_CONTROLLERS . '/' . $controllerName . '.php';
if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo "<h1>404 - Controller não encontrado</h1>";
    echo "<p>Arquivo <strong>{$controllerName}.php</strong> não existe em /controllers.</p>";
    exit;
}

require_once $controllerFile;

if (!class_exists($controllerName)) {
    http_response_code(500);
    echo "<h1>Erro - Classe do controller não encontrada</h1>";
    exit;
}

$controller = new $controllerName();

if (!method_exists($controller, $actionName)) {
    http_response_code(404);
    echo "<h1>404 - Ação não encontrada</h1>";
    echo "<p>O método <strong>{$actionName}()</strong> não existe no controller <strong>{$controllerName}</strong>.</p>";
    exit;
}

// Chama a action com os parâmetros da URL (carrinho/remover/14 etc.)
call_user_func_array([$controller, $actionName], $params);
