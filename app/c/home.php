<?php
class home extends base{
    function __construct()
    {
    	parent::__construct();
    }
	function index(){
		$param['u']=$this->check();
		$param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = 'welcome';
		$this->display('v/index',$param);
	}
}