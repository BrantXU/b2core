<?php
// 加载配置文件
require_once APP . 'config.php';

// 加载工具类库
load('lib/utility',false);
// 初始化数据库连接(如果配置了数据库)

// 基础控制器类
class base extends c {
	// 构造函数 - 检查数据库配置
	function __construct(){
		global $db_config,$db,$db_tenant;
            // 确保db_config全局变量已定义
            // 确保db_config全局变量已定义并正确加载
            if (!isset($GLOBALS['db_config'])) {
                error_log('数据库配置未找到');
                return;
            }

            $db_config = $GLOBALS['db_config'];
            // Include the db class definition
            // 初始化数据库连接
            $db = new db($db_config);
            // 将数据库连接对象存储在全局变量中，以便在其他地方使用
            $GLOBALS['db'] = $db;
            
            // 初始化租户数据库连接（如果有当前租户）
            if (isset($_SESSION['route_tenant_id'])) {
                //print_r($_SESSION);
                $tenantId = $_SESSION['route_tenant_id'];
                $tenantDbConfig = $GLOBALS['db_config'];
                // 确保租户数据库配置存在
                if (!is_array($tenantDbConfig)) {
                    error_log('租户数据库配置不存在');
                    return;
                }
                
                $tenantDbConfig['tenant_id'] = $tenantId;
                $db_tenant = new db($tenantDbConfig);
                $GLOBALS['db_tenant'] = $db_tenant;
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
            $param['tenant_id'] = $_SESSION['route_tenant_id'];
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
		

        // 从租户目录下的 men/目录中读取第一个.json文件，作为菜单数据
        $menu_data = array();
        if(isset($current_tenant['id'])) {
            $menu_dir = APP . '../data/' . $current_tenant['id'] . '/men/';
            // 获取目录下所有JSON文件
            $json_files = glob($menu_dir . '*.json');
            if(!empty($json_files)) {
                // 取第一个JSON文件
                $menu_file = $json_files[0];
                $menu_json = file_get_contents($menu_file);
                $menu_data = json_decode($menu_json, true);
            }
        }
        
        // 如果租户目录下没有菜单文件，则尝试从默认目录加载
        if(empty($menu_data)) {
            $default_menu_file = APP . '../data/default/menu.json';
            if(file_exists($default_menu_file)) {
                $menu_json = file_get_contents($default_menu_file);
                $menu_data = json_decode($menu_json, true);
                // 转换菜单数据格式
                $menu_data = convert_menu_data($menu_data);
            }
        }
        
        // 如果仍然没有菜单数据，则使用默认菜单
        if(empty($menu_data)) {
            $menu_data = array(
                array('name' => '注册', 'url' => tenant_url('user/reg/')),
                array('name' => '请登录', 'url' => tenant_url('user/login/')),
                array('name' => '租户管理', 'url' => tenant_url('tenant/')),
                array('name' => '配置管理', 'url' => tenant_url('config')),
                array('name' => '用户管理', 'url' => tenant_url('user')),
                array('name' => '实体管理', 'url' => tenant_url('entity'))
            );
        }
        
        $param['menu_data'] = $menu_data;

		$param['al_content'] = view($view,$param,TRUE);
		header("Content-type: text/html; charset=utf-8");
		view('v/template',$param);
	}
}
