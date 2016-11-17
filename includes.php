<?php

include_once '_ad2fw/common_includes.php';

/**
 * Автозагрузчик фреймворка будет динамически подключать файлы из этих папок
 * при создании классов.
 * Вообще файлы классов можно класть произвольно в указанные в $all_path папки -
 * автолоадер их найдет. Папки нужны для четкого понимания, что где лежит.
 * Наприме, в папке /controllers находятся файлы контроллеров страниц,
 * в папке /core - всякие там Project_Db и пр.
 * Можно добавлять произвольные папки
 */
$all_path = array(
    '/',
    '/controllers',
    '/core',
);


foreach ($all_path as $path){
    $path = dirname(__FILE__).$path;
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
}

/**
 * путь к шаблонам Smarty проекта
 */
define('RELATIVE_PATH_TO_TEMPLATES', dirname(__FILE__).'/views/tpl');

define('PROJECT_URL', Config::$project_url);
define('FULL_URL_TO_FW', Config::fw_url());
define('URL_TO_PROJECT_IMG', PROJECT_URL . '/views/img/');
