<?php
class mongostats extends Controller {

    public function run($req){
        $tpl = new Tpl_Obj();
        $tpl->assign('js', ['mongostats.js']);
        $tpl->display('common/page_header.tpl');
        $tpl->display('mongostats/preloader.tpl');
        $tpl->display('mongostats/plot.tpl');
        $tpl->display('common/page_footer.tpl');
    }

}