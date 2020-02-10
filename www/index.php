<?php
session_start();                                //Инициализация сессии
include 'sys/core/autoload.php';                //Автозагрузка классов
$app = new app\PhoneBook;
$app->setCfg();                                 //Конфигурация
if ($app->pdo) {
    $siteName = 'PhoneBook';
    $app->cfg = [
        'siteName' => $siteName,
        'adminMail' => 'phonebook@zadarma.com',
        'showJS' => 'down',
        'charset' => 'utf-8',
    ];
    $app->meta = [
        'title' => $siteName,
        'description' => $siteName,
        'viewport' => 'width=device-width, initial-scale=1',
    ];
    $css = [
        '//fonts.googleapis.com/css?family=Roboto:400,400i,700&amp;subset=cyrillic-ext',
        '//cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css',
        'css/fontawesome/all.min.css',
        'css/bootstrap-v4.min.css',
        'css/default.css',
        'css/style.css',
    ];
    $js = [
        '//ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js',
        '//cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js',
        'js/jquery.tablesorter.min.js',
        'js/jquery.maskedinput.min.js',
        'js/main.js',
    ];
    if ($css) $app->addCSS($css);
    if ($js) $app->addJS($js);
    $app->showHeaders();                            //Выставляем заголовки
    if (isset($_REQUEST['ajax'])) $app->ajax = 1;   //AJAX
    $app->init();                                   //Вывод содержимого
    $app->dbConnect('close');                 //Закрытие соединения с БД
}