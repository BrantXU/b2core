<?php
// 加载配置文件
require_once APP . 'config.php';

$conf = [];
// 加载工具类库
load('lib/utility',false);
// 初始化数据库连接(如果配置了数据库)

// 基础控制器类
class base extends c {

    public $menu_data = array();
    public $log = [];
	// 构造函数 - 检查数据库配置
	function __construct(){
		global $db_config,$db,$db_tenant,$conf,$tenant_id,$uri;
        $user = load('m/user_m')->check();
        if(!$user['id']&& $uri!='/default/user/login'){
            redirect(BASE . '/login/', '登录系统');
        }

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
        $tenantId =$tenant_id ?:'default';
        $_SESSION['route_tenant_id'] = $tenantId;
        $tenantDbConfig = $GLOBALS['db_config'];
        // 确保租户数据库配置存在
        if (!is_array($tenantDbConfig)) {
            error_log('租户数据库配置不存在');
            return;
        }
        $tenantDbConfig['tenant_id'] = $tenantId;
        $db_tenant = new db($tenantDbConfig);
        $GLOBALS['db_tenant'] = $db_tenant;
        $this->addBreadcrumb('首页', tenant_url('home/'));
        // 读入 conf.json 文件
        $conf_json = file_get_contents(APP.'../data/'.$tenantId.'/conf.json');
        if ($conf_json === false) {
            error_log('无法读取 conf.json 文件');
            return;
        }
        $conf = json_decode($conf_json, true);
    
        $menu_config_id = null;
        if (isset($conf['type_map']) && isset($conf['type_map']['menu'])) {
            // 获取菜单类型的第一个配置ID
            $menu_configs = $conf['type_map']['menu'];            
            if (!empty($menu_configs)) {
                $menu_config_id = key($menu_configs); // 获取第一个键作为配置ID
            }
        }
    
        // 从/data/{tenant_id}/conf/中读入菜单配置
        $menu_data = array();
        if(isset($tenantId) && !empty($menu_config_id)) {
            $conf_dir = APP . '../data/' . $tenantId . '/conf/';
            
            // 尝试读取JSON配置文件
            $json_file = $conf_dir . $menu_config_id . '.json';
            if (file_exists($json_file)) {
                $menu_json = file_get_contents($json_file);
                $menu_data = json_decode($menu_json, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('菜单JSON配置文件解析错误');
                    $menu_data = array();
                }
            } else {
                // 尝试读取YAML配置文件
                $yaml_file = $conf_dir . $menu_config_id . '.yaml';
                if (file_exists($yaml_file)) {
                    $menu_yaml = file_get_contents($yaml_file);
                    // 由于YAML文件实际存储的是JSON格式，这里使用json_decode解析
                    $menu_data = json_decode($menu_yaml, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        error_log('菜单YAML配置文件解析错误');
                        $menu_data = array();
                    }
                } else {
                    error_log('未找到菜单配置文件: ' . $menu_config_id);
                }
            }
        }
        
        // 如果租户目录下没有菜单文件，则尝试从默认目录加载
        if(empty($menu_data)) {
            $default_menu_file = APP . '../data/default/menu.json';
            if(file_exists($default_menu_file)) {
                $menu_json = file_get_contents($default_menu_file);
                $menu_data = json_decode($menu_json, true);
                // 注意：convert_menu_data函数未定义，暂时使用原始数据
                // $menu_data = convert_menu_data($menu_data);
                error_log('警告: convert_menu_data函数未定义，使用原始菜单数据');
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
                array('name' => '配置管理', 'url' => tenant_url('config')),
            );
        }

        $this->menu_data = $menu_data;
	}
	
    /**
     * 记录日志，可以接受字符串、数组或对象
     * @param mixed $data 要记录的数据
     */
    public function log($data)
  {
    // 如果是数组或对象，转换为JSON字符串
    if (is_array($data) || is_object($data)) {
      //$data = json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    $this->log[] = $data;
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
        $param['log'] = $this->log;
		header("Content-type: text/html; charset=utf-8");
		view('v/template',$param);
	}
    
    
    protected $breadcrumbs = [];

    public function addBreadcrumb($label, $url = '', $isActive = false)
    {
        $this->breadcrumbs[] = [
            'label' => $label,
            'url' => $url,
            'active' => $isActive
        ];
    }

    private function breadcrumb()
    {
        return $this->breadcrumbs;
    }
}
