<?php
// 加载工具类库
load('lib/utility',false);

// 基础控制器类
class base extends c {
	// 构造函数 - 检查数据库配置
	function __construct(){
		global $db_config;
		if(isset($db_config)){
		}else{
			echo '未设置数据库配置信息';
		}
	}
	
	// 检查用户登录状态
	function check() {
		$u = load('m/user_m')->check(); 
		return $u;
	}

	// 显示视图的统一方法
	function display($view='v/index',$param = array()){
		$param['al_content'] = view($view,$param,TRUE);
		header("Content-type: text/html; charset=utf-8");
		view('v/template',$param);
	}
}
