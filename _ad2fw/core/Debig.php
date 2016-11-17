<?php
class Debig{

    /**
     * Просмотр дампа переменных в нормальном виде
     * @static
     * @param $data
     * @param $die
     */
    public static function dump($data, $die = false){
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        if($die){
            die($die);
        }
    }

    /**
     * Просмотр переменной/массива в нормальном виде
     * @static
     * @param $data
     * @param $die
     */
    public static function view($data, $die = false){
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if($die){
            die($die);
        }
    }

    /**
     * Сообщение
     * @param $text
     * @param int $hlevel - цифра у тега <h*>, в который оборачивается сообщение
     */
    public static function msg($text, $hlevel = 3){
        echo '<h' . $hlevel . '>' . $text . '</h' . $hlevel . '>';
    }
}
 
