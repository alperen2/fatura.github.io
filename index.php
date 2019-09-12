<?php

require_once './vendor/autoload.php';

require_once './config.php';

$route = $_SERVER['REQUEST_URI'];

$explode = explode('/', $route);

$route = array_values(array_filter($explode));

if (SUBFOLDER) array_shift($route);


$page = $route[0] ?? 'index';
unset($route[0]);
$act = $route[1] ?? false;
unset($route[1]);

$params = $route ? array_values($route) : false;

foreach (glob("./helpers/*.php") as $helperFile) {
    require $helperFile;
};
// require_once './router.php';
$file = HOME_PATH . "/controllers/{$page}.php";


if (!isset($_SESSION['is_login']) && $page != "login") {
    redirect('login');
}

if (file_exists($file)) {
    require HOME_PATH . "/controllers/{$page}.php";
} else {
    $error = [
        'code' => '404',
        'header' => 'NOT FOUND',
        'message' => 'Aradığınız sayfa bulunamadı!',
    ];
    echo $twig->render('error.html', $error);
}
