<?php

define('HOME_PATH', realpath('.'));
define('SUBFOLDER', false);
define('SITE', 'http://favoritoglu.com/fatura/');

ob_start();
session_start();

$db_info = [
    'host' => 'localhost',
    'name' => 'favoritoglu_fatura',
    'user' => 'favoritoglu_root',
    'password' => 'M"5x0~Vy'
];

try {
    $db  = new PDO("mysql:host={$db_info['host']};dbname={$db_info['name']}", $db_info['user'], $db_info['password']);
} catch (PDOException $e) {
    throw $e;
}

$loader = new \Twig\Loader\FilesystemLoader('./templates');
$twig = new \Twig\Environment($loader, ['debug' => true]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

$twig->addGlobal('SITE_URL', SITE);

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'debug' => true
]);
