<?php
class Tpl_Obj {

    private $tpl_obj;

    public function __construct(){
        $this->tpl_obj = new Prototype_Tpl_Obj();
    }

    public function assign($var_name, $value){
        return $this->tpl_obj->assign($var_name, $value);
    }

    public function fetch($template){
        return $this->tpl_obj->fetch($template);
    }

    public function display($template){
        return $this->tpl_obj->display($template);
    }
}