<?php
ini_set( 'display_errors', 0 );
error_reporting( -1 );
if(ECHO_ERROR){
    set_error_handler( array( 'Error_Handler', 'capture_normal' ) );
    set_exception_handler( array( 'Error_Handler', 'capture_exception' ) );
    register_shutdown_function( array( 'Error_Handler', 'captureShutdown' ) );
}
class Error_Handler {

    protected static $error_styles = <<<html
<style>
.Fatal_Error {background-color: red; color: #fff;}
.Warning {background-color: orange; color: #000;}
.Notice {background-color: #ffff00; color: #000;}
.Strict_recomendation {background-color: #c3ff14; color: #000;}
.Captured_Fatal_Attention {background-color: red; color: #fff;}
.Deprecated {background-color: #ffff00; color: #000;}
.msg {font-weight: bold;}
.error_block {padding: 5px; margin: 5px; border-radius: 3px;
font-family: Verdana, Arial, helvetica, sans-serif; font-size: 80%;}
.error_block p {margin: 0;}
.trace {background-color: #ebebeb}

</style>
html;


    protected static function error_levels($level){
        $error_levels = array(
            1 => 'Fatal_Error',
            2 => 'Warning',
            8 => 'Notice',
            32 => 'Warning',
            256 => 'Trigger_Error',
            512 => 'Trigger_Warning',
            1024 => 'Trigger_Notice',
            2048 => 'Strict_(recomendation)',
            4096 => 'Captured_Fatal_(Attention!)',
            8192 => 'Deprecated',
            16384 => 'Trigger_Deprecated',
    );
        return $error_levels[$level];

/*        if(in_array($level, $error_levels)){
            return $error_levels[$level];
        }
        return '';*/
    }

    protected static function error_msg($level){
        return str_replace(array('_'), ' ', self::error_levels($level));
    }

    protected static function error_class($level){
        return str_replace(array('(', ')', '!'), '', self::error_levels($level));
    }

    protected static function print_error($level, $message, $file, $line){
        echo self::$error_styles;
        $msg = self::error_msg($level);
        $class = self::error_class($level);
        $full_msg = <<<html
<div class="$class error_block">
    <p>$msg (level $level)</p>
    <p class="msg">$message</p>
    <p>File <strong>$file</strong></p>
    <p>Line <strong>$line</strong></p>
</div>
html;
        echo $full_msg;
    }

    public static function capture_normal($level, $message, $file, $line){
        self::print_error($level, $message, $file, $line);
    }

    public static function capture_exception(Exception $exception){
        $level = 1;
        $message = 'Not Handled Exceprion';
        $file = $exception->getFile();
        $line = $exception->getLine();
        self::print_error($level, $message, $file, $line);
        echo '<div class="error_block trace">';
        echo 'Exception Trace:<br>';
        echo str_replace(PHP_EOL, '<br>', $exception->getTraceAsString());
        echo '</pre>';
    }

    public static function captureShutdown(){
        $error = error_get_last();
        if($error){
            self::print_error($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
}
/**
 * Пути к классам фреймворка
 */
$all_path = array(
    '/core',
    '/Smarty',
);
foreach ($all_path as $path){
    $path = dirname(__FILE__).$path;
    set_include_path(get_include_path().PATH_SEPARATOR.$path);
}

/**
 * Подключение шаблонов и Smarty
 * Все нужные пути прописываются и файлы подключаются при создании объекта Smarty_Obj
 * Все пути относительные. Прописать требуется только пути к Smarty и шаблонам
 * Все пути - относительно корня веб-директории
 */

define('RELATIVE_PATH_TO_SMARTY', '/Smarty');

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).RELATIVE_PATH_TO_SMARTY);

function class_loader($className){

    //предотвращение конфликта с автолоадером Smarty
    if(strstr($className, 'Smarty')){
        $className = strtolower($className);
    }

    include_once $className.'.php';
}

spl_autoload_register('class_loader');


