<?php
class home extends base{
    function __construct()
    {
    	parent::__construct();
    }
    public function index():void {
        $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = 'welcome';
        $this->display('v/index', $param);
    }
}