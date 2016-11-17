<?php
class SLMongo {

    protected $collection;

    protected $collection_name;
    public $field_name;
    public $regexp;

    public function __construct($collection){
        try{
            //$db = new MongoClient('mongodb://starliner:Dakbypwats@109.230.128.152/starliner');
            //$db = new MongoClient('mongodb://starliner:Dakbypwats@31.186.100.112/starliner');
            $db = new MongoClient('mongodb://starliner:Dakbypwats@109.230.128.152/starliner');
            $this->collection = $db->selectCollection('starliner', $collection);
            $this->collection_name = $collection;
        }
        catch (Exception $e) {
            Debig::view($e->getMessage(), 1);
        }
    }

    public function api_count($start_timestamp, $stop_timestamp, $tictic_interval, $regexp){
        $tictic_array = [];
        $time_counter = $start_timestamp;

        $db = new Alm_Db();

        //погрешность сравнения интервалов в сек
        $delta = 20;

        $tictic_array_from_db = json_decode($db->json_select($regexp, $this->collection_name, $tictic_interval), 1);
        $time_array = array_keys($tictic_array_from_db);
        $json_time_max = max($time_array);
        $json_time_min = min($time_array);

        if($stop_timestamp < $json_time_max) //то есть часть массива можно взять из кеша, массив в БД не старый
        {
            if($start_timestamp <= $json_time_max) //все требуемые точки есть в кеше, ситуация при зумировани
            {
                $zoom_tictic_array = [];
                foreach ($tictic_array_from_db as $point_timestamp => $point_value)
                {
                    if ($point_timestamp < $start_timestamp && $point_timestamp > $stop_timestamp)
                    {
                        $zoom_tictic_array[$point_timestamp] = $point_value;
                    }
                }

                return $zoom_tictic_array;
            }

            $ticitic_new_points_count = round(($start_timestamp - $json_time_max) / $tictic_interval);
            $json_count_max_index = count($tictic_array_from_db) - 1;
            for($i = 0; $i < $ticitic_new_points_count; $i++) {
                unset($tictic_array_from_db[$json_count_max_index - $i]);//удаляем самые старые значения
            }

            $tictic_array = $tictic_array_from_db;//берем все из кеша

            //ключевой момент - из монго заберем только точки, которые по времени лежат между
            //$start_timestamp и $json_time_max
            $stop_timestamp = $start_timestamp - $tictic_interval * $ticitic_new_points_count + 1;
        }

/*        //то есть в json - наш массив, не старый
        if(($start_timestamp > $json_time_max) && ($start_timestamp - $json_time_max) < ($tictic_interval + $delta)){
            unset($tictic_array_from_db[$json_time_min]);//удаляем самое дальнее значение
            $tictic_array = $tictic_array_from_db;//берем все из кеша

            //ключевой момент - из монго заберем только самый ближний интервал
            $stop_timestamp = $start_timestamp - $tictic_interval;//минусуем дельту на всяк случай. Множно и -1
        }*/

        while($time_counter > $stop_timestamp){
            $criteria = [
                $this->field_name => new MongoRegex($regexp),
                'date' => ['$lte' => new MongoDate($time_counter), '$gte' => new MongoDate($time_counter - $tictic_interval)]
            ];

            $tictic_array[$time_counter] = $this->collection->find($criteria)->count();
            $time_counter -= $tictic_interval;
        }



        //пихаем все обратно в кеш
        $db->json_insert($regexp, json_encode($tictic_array), $this->collection_name, $tictic_interval);

        //отправляем предупреждения
        $mail_trigger = $db->get_value($this->collection_name);
        $current_alarm = $this->gate_alarm_check();
        if($current_alarm)//если есть авария
        {
            if(!$mail_trigger)//если почта для данного шлюза не отправлялась
            {
                $this->alarm_mail('alarm');
                //сохраняем то, что почта об аварии для данного шлюза отправлена
                $db->set_value($this->collection_name, 1);
            }
        }
        else //аварии нет
        {
            if($mail_trigger)//была отправлена почта об аварии
            {
                $this->alarm_mail('zero');
                //сохраняем то, что почта об окончании аварии для данного шлюза отправлена, и больше почту отправлять не надо
                $db->set_value($this->collection_name, 0);
            }
        }

        return $tictic_array;
    }

    private function gate_alarm_check()
    {
        $db = new Alm_Db();
        $gates = Log_Config::gates();
        $total_gate_regexp = $gates[$this->collection_name]['total_regexp'];
        $error_gate_regexp = $gates[$this->collection_name]['error_regexp'];
        $total_gate_realtime_data = array_values($db->gate_realtime_values($this->collection_name, $total_gate_regexp));
        $error_gate_realtime_data = array_values($db->gate_realtime_values($this->collection_name, $error_gate_regexp));
        $alarm_counter = 0;
        for($i = 0; $i < Log_Config::$count_alarm_level_points; $i++)
        {
            $error_level = $error_gate_realtime_data[$i] / $total_gate_realtime_data[$i];//уровень ошибки, в долях,f.e. 0.03
            if($total_gate_realtime_data[$i] > Log_Config::$alarm_total_level // если общее число запросов выше порога
                && $error_level > Log_Config::$gate_alarm_level) //и доля ошибок выше уровня тревоги
            {
                $alarm_counter ++; //добавим ошибку
            }
        }
        if($alarm_counter > Log_Config::$count_alarm_level_points) //заданное число ошибок подряд
        {
            return true;
        }

        return false;
    }

    private function alarm_mail($type)
    {
        $gate_name = Log_Config::gates()[$this->collection_name]['title'];
        foreach (Log_Config::email_list() as $email)
        {
            $to = $email;
            $subject_alarm = 'Авария ' . $gate_name;
            $message_alarm = <<<text
Аварийная ситуация на шлюзе $gate_name
Обратите внимание!
text;
            $subject_zero = 'Окончание аварии ' . $gate_name;
            $message_zero = <<<text
Завершение аварийной ситуации на шлюзе $gate_name
text;
            if($type == 'alatm')
            {
                $subject = $subject_alarm;
                $message = $message_alarm;
            }
            else
            {
                $subject = $subject_zero;
                $message = $message_zero;
            }
            mail($to, $subject, $message);
        }
    }
}

class SLMongo_backup {

    protected $collection;

    protected $collection_name;
    public $field_name;
    public $regexp;

    public function __construct($collection){
        try{
            //$db = new MongoClient('mongodb://starliner:Dakbypwats@109.230.128.152/starliner');
            $db = new MongoClient('mongodb://starliner:Dakbypwats@31.186.100.112/starliner');
            $this->collection = $db->selectCollection('starliner', $collection);
            $this->collection_name = $collection;
        }
        catch (Exception $e) {
            Debig::view($e->getMessage(), 1);
        }
    }

    public function api_count($start_timestamp, $stop_timestamp, $tictic_interval, $regexp){
        $tictic_array = [];
        $time_counter = $start_timestamp;
        while($time_counter > $stop_timestamp){
            $criteria = [
                $this->field_name => new MongoRegex($regexp),
                'date' => ['$lte' => new MongoDate($time_counter), '$gte' => new MongoDate($time_counter - $tictic_interval)]
            ];

            $tictic_array[$time_counter] = $this->collection->find($criteria)->count();
            $time_counter -= $tictic_interval;
        }

        return $tictic_array;
    }
}