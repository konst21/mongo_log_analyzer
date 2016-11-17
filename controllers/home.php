<?php
class home extends Controller{

    public function run($req){
        $tpl = new Tpl_Obj();
        $tpl->display('common/page_header.tpl');
        $tpl->display('home/hello.tpl');
        $tpl->display('common/page_footer.tpl');
    }

}