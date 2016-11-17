<?php
class Config {

    /**
     * корневой URL веб-приложения, НЕ ДОМЕН
     * f.e. домен foo.com, приложение лежит в папке bar
     * то указать нужно именно полный веб-путь к приложению, а именно
     * 'http://foo.com/bar'
     * БЕЗ СЛЕША В КОНЦЕ
     * @var string
     */
    public static $project_url = 'https://alarmyzer.starliner.ru';

    /**
     * Путь к фреймворку. По умолчанию он лежит в корне приложения в папке ad2fw
     * Нужен для загрузки общих CSS, JS - bootstrap или jquery например
     * @return string
     */
    public static function fw_url(){
        return self::$project_url . '/_ad2fw';
    }

    /**
     *
     */
    public static function gates(){

    }

    public static $db_host = 'localhost';
    public static $db_name = 'alarmyzer';
    public static $db_user = 'konst20';
    public static $db_pass = 'rbzirj25';



}
