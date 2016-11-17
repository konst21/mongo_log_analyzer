<?php
class mongostats_data extends Controller {
    public function run($req){
        $out = [];
        $gate = $req[0];
        $days = $req[2];
        $interval = $req[4];
        $collection = $gate;
        $search_array = Log_Config::gates()[$gate];

        try{
            $db = new SLMongo($collection);
            $db->field_name =$search_array['field_name'];
            $db->regexp = $search_array['regexp'];
            /*        $out[] = [
                        'label' => $search_array['label'],
                        'data' => $db->month_errors()
                    ];*/
            $plot_start_timestamp = ($_POST['plot_start_timestamp'])?$_POST['plot_start_timestamp']:time();
            $plot_finish_timestamp = ($_POST['plot_finish_timestamp'])?$_POST['plot_finish_timestamp']:time()-$days*86400;
            $super_out_data = [];
            $super_out_data['total'] = $db->api_count($plot_start_timestamp, $plot_finish_timestamp, $interval*60,
                $search_array['total_regexp']);
            $super_out_data['errors'] = $db->api_count($plot_start_timestamp, $plot_finish_timestamp, $interval*60,
                $search_array['error_regexp']);
            unset($db);

        }
        catch(Exception $e){
            echo $e->getMessage();
        }
        //всего обращений к шлюзу - засовываем в выходной массив
        $out[] = [
            'type' => 'total',
            'label' => 'Всего запросов',//отображение на подсказке
            'data' => $super_out_data['total'],//данные
            'min_tick_size_time_period' => 'minute',//шкала - не роляет
            'min_tick_size' => $interval,//тоже шкала, тоже не роляет
            'scale' => 1, //масштаб - нужен для обратного пересчета значений
        ];

        //ошибок на шлюзе
        $out[] = [
            'type' => 'errors',
            'label' => 'Ошибок',
            'data' => $super_out_data['errors'],
            'min_tick_size_time_period' => 'minute',
            'min_tick_size' => $interval,
            'scale' => 1,
        ];

        $errors_stat = [];
        $total_max = 0;
        foreach($super_out_data['total'] as $time_stamp => $total){
            $errors_stat[$time_stamp] = $super_out_data['errors'][$time_stamp] / $total;
            if($total_max < $total){
                $total_max = $total;
            }
        }
        //масштабируем процентное значение ошибок, иначе его на графике совсем видно не будет
        foreach($errors_stat as $timestamp => $persentage_value){
            $errors_stat[$timestamp] = round($persentage_value * $total_max * Log_Config::$persentage_scale);
        }

        //процент ошибок - обратите внимание на масштаб, см яваскрипт mongostats.js -
        //там данные на полсказке пересчитываются обратно
        $out[] = [
            'type' => 'persentage',
            'label' => '% Ошибок',
            'data' => $errors_stat,
            'min_tick_size_time_period' => 'minute',
            'min_tick_size' => $interval,
            'scale' => $total_max * Log_Config::$persentage_scale,
        ];


        //Debig::view($out);
        echo json_encode($out);
    }
}class mongostats_data_backup extends Controller {
    public function run($req){
        $out = [];
        $gate = $req[0];
        $days = $req[2];
        $interval = $req[4];
        $collection = $gate;
        $search_array = Log_Config::gates()[$gate];

        try{
            $db = new SLMongo($collection);
            $db->field_name =$search_array['field_name'];
            $db->regexp = $search_array['regexp'];
            /*        $out[] = [
                        'label' => $search_array['label'],
                        'data' => $db->month_errors()
                    ];*/
            $plot_start_timestamp = ($_POST['plot_start_timestamp'])?$_POST['plot_start_timestamp']:time();
            $plot_finish_timestamp = ($_POST['plot_finish_timestamp'])?$_POST['plot_finish_timestamp']:time()-$days*86400;
            $super_out_data = [];
            $super_out_data['total'] = $db->api_count($plot_start_timestamp, $plot_finish_timestamp, $interval*60, $search_array['total_regexp']);
            $super_out_data['errors'] = $db->api_count($plot_start_timestamp, $plot_finish_timestamp, $interval*60, $search_array['error_regexp']);
            unset($db);

        }
        catch(Exception $e){
            echo $e->getMessage();
        }
        //всего обращений к шлюзу - засовываем в выходной массив
        $out[] = [
            'type' => 'total',
            'label' => 'Всего запросов',//отображение на подсказке
            'data' => $super_out_data['total'],//данные
            'min_tick_size_time_period' => 'minute',//шкала - не роляет
            'min_tick_size' => $interval,//тоже шкала, тоже не роляет
            'scale' => 1, //масштаб - нужен для обратного пересчета значений
        ];

        //ошибок на шлюзе
        $out[] = [
            'type' => 'errors',
            'label' => 'Ошибок',
            'data' => $super_out_data['errors'],
            'min_tick_size_time_period' => 'minute',
            'min_tick_size' => $interval,
            'scale' => 1,
        ];

        $errors_stat = [];
        $total_max = 0;
        foreach($super_out_data['total'] as $time_stamp => $total){
            $errors_stat[$time_stamp] = $super_out_data['errors'][$time_stamp] / $total;
            if($total_max < $total){
                $total_max = $total;
            }
        }
        //масштабируем процентное значение ошибок, иначе его на графике совсем видно не будет
        foreach($errors_stat as $timestamp => $persentage_value){
            $errors_stat[$timestamp] = $persentage_value * $total_max * Log_Config::$persentage_scale;
        }

        //процент ошибок - обратите внимание на масштаб, см яваскрипт mongostats.js -
        //там данные на полсказке пересчитываются обратно
        $out[] = [
            'type' => 'persentage',
            'label' => '% Ошибок',
            'data' => $errors_stat,
            'min_tick_size_time_period' => 'minute',
            'min_tick_size' => $interval,
            'scale' => $total_max * Log_Config::$persentage_scale,
        ];


        //Debig::view($out);
        echo json_encode($out);
    }
}
