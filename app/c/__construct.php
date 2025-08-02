<?php
// 加载配置文件
require_once APP . 'config.php';

// 加载工具类库
load('lib/utility',false);
// 初始化数据库连接(如果配置了数据库)

// 基础控制器类
class base extends c {

    public $menu_data = array();
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


        
        // 从租户目录下的 men/目录中读取第一个.json文件，作为菜单数据
        $menu_data = array();
        if(isset($tenantId)) {
            $menu_dir = APP . '../data/' . $tenantId . '/men/';
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

        $this->menu_data = $menu_data;
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
		

        
        $param['menu_data'] = $this->menu_data;
        $param['breadcrumb'] = $this->breadcrumb();
		$param['al_content'] = view($view,$param,TRUE);
		header("Content-type: text/html; charset=utf-8");
		view('v/template',$param);
	}
    
    
    private function breadcrumb()
    {
        $breadcrumb = [];
        // 获取当前页面路径
        $current_path = $_SERVER['REQUEST_URI'] ?? '';
        // 移除查询参数
        if (strpos($current_path, '?') !== false) {
            $current_path = substr($current_path, 0, strpos($current_path, '?'));
        }
        // 移除租户ID前缀
        $tenant_id = $_SESSION['current_tenant'] ?? '';
        if ($tenant_id && strpos($current_path, '/' . $tenant_id) === 0) {
            $current_path = substr($current_path, strlen('/' . $tenant_id));
        }
        // 移除开头的斜杠
        $current_path = ltrim($current_path, '/');
        // 分割路径获取模块名
        $path_parts = explode('/', $current_path);
        $module_name = $path_parts[0] ?? '';
        $action_name = $path_parts[1] ?? '';
        
        // 从菜单数据中查找模块名称
        $menu_data = $this->menu_data ?? [];
        $module_title = '首页'; // 默认值
        $module_url = tenant_url('');
        
        if (!empty($menu_data) && !empty($module_name)) {
            foreach ($menu_data as $item) {
                // 检查是否为目录类型
                $is_dir = isset($item['type']) && $item['type'] === 'dir';
                
                if ($is_dir && isset($item['children']) && is_array($item['children'])) {
                    foreach ($item['children'] as $sub_item) {
                        $item_mod = isset($sub_item['mod']) ? $sub_item['mod'] : '';
                        if ($item_mod === $module_name) {
                            $module_title = $sub_item['title'] ?? $sub_item['name'] ?? $module_name;
                            $module_url = tenant_url($module_name);
                            break 2; // 跳出两层循环
                        }
                    }
                } else {
                    $item_mod = isset($item['mod']) ? $item['mod'] : '';
                    if ($item_mod === $module_name) {
                        $module_title = $item['title'] ?? $item['name'] ?? $module_name;
                        $module_url = tenant_url($module_name);
                        break;
                    }
                }
            }
        }
        
        // 添加面包屑数据
        $breadcrumb[] = ['label' => '首页', 'url' => tenant_url(''), 'active' => false];
        
        // 如果有模块名称且不是首页，则添加模块面包屑
        if (!empty($module_name) && $module_name !== 'home') {
            $breadcrumb[] = ['label' => $module_title, 'url' => $module_url, 'active' => false];
        }
        
        // 添加当前页面面包屑
        if (!empty($action_name)) {
            // 根据操作名称生成标签
            $action_label = [
                'index' => '列表',
                'add' => '新增',
                'create' => '新增',
                'edit' => '编辑',
                'view' => '查看',
                'delete' => '删除',
                'import' => '导入',
                'export' => '导出'
            ][$action_name] ?? ucfirst($action_name);
            
            // 如果模块名称不为空，则组合标签
            if (!empty($module_name)) {
                $action_label = $module_title . $action_label;
            }
            
            $breadcrumb[] = ['label' => $action_label, 'url' => '', 'active' => true];
        } elseif (empty($module_name) || $module_name === 'home') {
            // 如果是首页，则设置首页为活动状态
            $breadcrumb[0]['active'] = true;
        } else {
            // 如果只有模块名称没有操作，则设置模块为活动状态
            $breadcrumb[count($breadcrumb) - 1]['active'] = true;
        }
        
        return $breadcrumb;
    }
}
