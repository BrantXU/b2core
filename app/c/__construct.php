<?php
load('lib/utility',false);
class base extends c{
	function __construct(){
		global $db_config;
		if(isset($db_config)){
		}else{
			echo '未设置数据库配置信息';
		}
	}
	
  function check()
  {
    $u = load('m/user_m')->check(); 
    return $u;
  }
  function display($view='v/index',$param = array()){
	    $param['al_content'] = view($view,$param,TRUE);
	    header("Content-type: text/html; charset=utf-8");
	    view('v/template',$param);
  }
}
