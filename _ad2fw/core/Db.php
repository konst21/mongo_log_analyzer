<?php
class Db{

    /**
     * Принцип: при создании объекта сразу соединяемся с БД, получаем PDO handler
     * и далее его используем для исполнения SQL
     */

    /**
     * Параметры соединения с БД
     */
    private $db_host = '';
    private $db_name = '';
    private $db_user = '';
    private $db_pass = '';

    /**
     * Идентификатор соединения с БД
     */
    protected $handler;

    /**
     * При создании объекта сразу соединяемся с БД, handler потом используется методами класса
     */
    public function __construct(){

        if(!class_exists('Config')){
            throw new Exception('Class Config not found');
        }

        $this->db_host = Config::$db_host;
        $this->db_name = Config::$db_name;
        $this->db_user = Config::$db_user;
        $this->db_pass = Config::$db_pass;

        $this->handler = $this->get_db_handler();
        $this->handler->exec('SET NAMES UTF8');
    }

    /**
     * @static
     * @param  $db_host
     * @param  $db_name
     * @param  $db_user
     * @param  $db_pass
     * @return PDO
     */
    private function db_connect($db_host, $db_name, $db_user, $db_pass) {
        try {
           $db_handler = new PDO('mysql:host='.$db_host.';dbname='.$db_name.'', $db_user, $db_pass);
        }
        catch(PDOException $e) {
            echo 'Db error '.$e->getMessage();
            die();
        }

        return $db_handler;
    }

    /**
     * @return PDO
     */
    private function get_db_handler(){
        $handler = $this->db_connect($this->db_host, $this->db_name, $this->db_user, $this->db_pass);
        return $handler;
    }

    /**
     * Подготовка и выполнение SQL-запросов с параметрами, см. примеры
     * @throws Exception
     * @param  $sql
     * @param array $data
     * @return PDOStatement
     */
    protected function sql_prepare_and_execute($sql, $data = array()){

        $handler = $this->handler;

        $st = $handler->prepare($sql);// $st - PDOStatement
        $result = $st->execute($data);
        if(!$result){
            throw new Exception('DB error: '.implode($st->errorInfo(), ' '));
        }

        return $st;
    }

    /**
     * Ф-я выбирает все записи и образует массив вида
     * array(
     * [0] => array(...)
     * [1] => array(...)
     * )
     * где каждый вложенный массив - строка (запись) таблицы
     * возвращает false, если число записей в выборке == 0,
     * @param $sql
     * @param array $data
     * @return array|bool
     */
    protected function select_all($sql, $data = array()){
        $st = $this->sql_prepare_and_execute($sql, $data);
        $raw_array = $st->fetchAll(PDO::FETCH_ASSOC);
        if(!is_array($raw_array) || count($raw_array) == 0){
            return false;
        }
        return $raw_array;
    }

    /**
     * ф-я применяется, для выборки одной записи,
     * возвращает ассоциативный массив, ключи - имена полей таблицы, значения - значения полей таблицы
     * если в выборке несколько записей, можно указать, какая именно по счету интересует, указав параметр $rec_num
     * array(
     * [field1] => $value1,
     * [field2] => $value2
     * ......
     * )
     * @param $sql
     * @param array $data
     * @param int $rec_num
     * @return bool
     */
    protected function select_one_rec($sql, $data = array(), $rec_num = 0){
        $st = $this->sql_prepare_and_execute($sql, $data);
        $raw_array = $st->fetchAll(PDO::FETCH_ASSOC);
        if(!is_array($raw_array) || count($raw_array) == 0){
            return false;
        }
        if($rec_num > count($raw_array) - 1){
            return false;
        }
        return $raw_array[$rec_num];
    }

    /**
     * Ф-я используется, если требуется обеспечить уникальность каждого элемента выборки
     * Ф-я преобразует результат выборки PDO из БД в ассоциативный массив вида
     * [id0] => array(id0, ...)
     * [id1] => array(id1, ...)
     * [id2] => array(id2, ...)
     * где каждый вложенный массив - строка (запись) таблицы
     * возвращает false, если число записей в выборке == 0,
     * либо массив
     * @param $sql
     * @param $data
     * @param string $id_field_name
     * @return array|bool
     */
    protected function select_all_with_id($sql, $data = array(), $id_field_name = 'id'){
        $st = $this->sql_prepare_and_execute($sql, $data);
        $raw_array = $st->fetchAll(PDO::FETCH_ASSOC);
        if(!is_array($raw_array) || count($raw_array) == 0){
            return false;
        }
        $outer_array = array();
        foreach($raw_array as $item){
            $outer_array[trim($item[$id_field_name])] = $item;
        }

        return $outer_array;
    }

    /**
     * Ф-я преобразует результат выборки PDO из БД в простой массив вида
     * [0] => value0
     * [1] => value1
     * [2] => value2
     * Применяется при выборке одного поля (столбца) из БД
     * возвращает false, если число записей в выборке == 0,
     * либо массив
     * @param $sql
     * @param $data
     * @param bool $field_name
     * @param int $field_index
     * @return array|bool
     */
    protected function select_one_field($sql, $data = array(), $field_name = false, $field_index = 0){
        $st = $this->sql_prepare_and_execute($sql, $data);
        $raw_array = $st->fetchAll(PDO::FETCH_NUM);
        if(!is_array($raw_array) || count($raw_array) == 0){
            return false;
        }

        $outer_array = array();

        if(!$field_name){
            $raw_array = array_values($raw_array);
            foreach($raw_array as $item){
                $outer_array[] = $item[$field_index];
            }

        }
        else{
            foreach($raw_array as $item){
                $outer_array[] = $item[$field_name];
            }
        }

        return $outer_array;
    }

    /**
     * выборка одного значения
     * @param $sql
     * @param array $data
     * @return bool|string
     */
    protected function select_value($sql, $data = array()){
        $st = $this->sql_prepare_and_execute($sql, $data);
        $raw_value = $st->fetchColumn();
        if(!$raw_value || is_null($raw_value) || empty($raw_value)){
            return false;
        }
        return $raw_value;
    }

}
 
