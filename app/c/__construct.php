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
        $param['u'] = $this->check();
        
        // 获取当前租户信息
        // 首先检查路由中指定的租户ID
        if(isset($_SESSION['route_tenant_id'])) {
            $tenant_m = load('m/tenant_m');
            $current_tenant = $tenant_m->getTenant($_SESSION['route_tenant_id']);
            $param['current_tenant'] = $current_tenant;
            
            // 将路由租户ID设置为当前租户
            $_SESSION['current_tenant'] = $_SESSION['route_tenant_id'];
        } elseif(isset($_SESSION['current_tenant'])) {
            $tenant_m = load('m/tenant_m');
            $current_tenant = $tenant_m->getTenant($_SESSION['current_tenant']);
            $param['current_tenant'] = $current_tenant;
        } else {
            // 如果用户已登录但未选择租户，则默认属于 default 租户
            if($param['u']['id'] > 0) {
                redirect(BASE . '/tenant/enter/default', '请选择一个租户');
                return;
            }
            $param['current_tenant'] = null;
        }
		
		$param['al_content'] = view($view,$param,TRUE);
		header("Content-type: text/html; charset=utf-8");
		view('v/template',$param);
	}
}
