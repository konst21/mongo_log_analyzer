<?php
/**
 * ЧПУ
 * имеет вид /controller/method/data
 * контроллер по-умолчанию (если не указан или не существует) - home()
 * метод по-умолчанию (если не указан или не существует) - run()
 */
define('ECHO_ERROR', false );
include_once 'includes.php';

$uri = $_SERVER['REQUEST_URI'];

$raw = explode('?',$uri);//вдруг кто сунул чистый $_GET запрос

$req1 = explode('/', $raw[0]);
unset($req1[0]);//убираем первый слеш
$req = array_values($req1);

if(isset($req[0]) && !empty($req[0])){
    if($req[0] && class_exists($req[0], true)){//это контроллер
        $controller = $req[0];
        unset($req[0]);
        if(isset($req[1]) && !empty($req[1])){ //в запросе еще что-то есть
            if(method_exists($controller, $req[1])){//это метод контроллера
                $method = $req[1];
                unset($req[1]);
                $c = new $controller();
                $req = array_values($req);//это чтобы массив данных, передаваемый методу контроллера, начинался с индекса 0
                $c->$method($req); //передаем методу оставшиеся куски запроса - параметры
            }
            else{ //это не метод контроллера - вызываем метод контроллера по умолчанию и передаем ему данные
                $c = new $controller();
                $req = array_values($req);
                $c->run($req);
            }
        }
        else{//в запросе более ничего нет - вызываем метод контроллера по-умолчанию и суем ему пустой массив в кач. аргуметна
            $c = new $controller();
            $c->run(array());
        }
    }
    else{ //это был не контроллер
        if(method_exists('home', $req[0])){//это метод контроллера home
            $method = $req[0];
            unset($req[0]);
            $req = array_values($req);
            $i = new home();
            $i->$method($req);//передаем методу оставшиеся куски запроса
        }
        else{ //это не метод home() - передаем запрос контроллеру по умолчанию и суем ему оставшиеся данные
            $i = new home();
            $req = array_values($req);
            $i->run($req);
        }
    }
}
else {//в запросе нет ничего - вызываем индекс с методом по умолчанию
    $c = new home();
    $c->run(array());
}
