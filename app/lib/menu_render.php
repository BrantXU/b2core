<?php 
/**
 * 渲染顶部主菜单
 * @param array $menu_data 菜单数据
 * @return string HTML代码
 */
function render_top_menu($menu_data) {
    $html = '<ul class="uk-navbar-nav">';
    foreach ($menu_data as $key => $item) {
        // 跳过隐藏菜单项

        if(in_array($key , ['id','key']))continue;
        if (isset($item['hidden']) && $item['hidden'] === true) {
            continue;
        }

        $has_children = isset($item['children']) && !empty($item['children']);
        $is_dir = isset($item['type']) && $item['type'] === 'dir';
        $item_path = isset($item['mod']) ? $item['mod'] : $key;
        $active = is_current_path($item_path) ? 'uk-active' : '';

        if ($is_dir && $has_children) {
            // 目录类型菜单，添加悬浮子菜单
            $html .= '<li class="uk-parent">';
            $html .= '<a href="#" class="'.$active.'" >'.htmlspecialchars($item['title'] ?? $item['name'] ?? 'Menu Item').($is_dir ? '<span uk-icon="icon: chevron-down; ratio: 0.7" class="uk-margin-remove"></span>' : '').'</a>';
            $html .= '<div class="uk-dropdown uk-dropdown-navbar" data-uk-dropdown>';
            $html .= '<ul class="uk-nav uk-nav-navbar">';
            // 只显示一级子菜单
            foreach ($item['children'] as $key => $child) {
                $child_path = isset($child['mod']) ? $child['mod'] : $key;
                $child_active = is_current_path($child_path) ? 'uk-active' : '';
                $html .= '<li class="' . $child_active . '">';
                $html .= '<a href="' . tenant_url($child_path) . '">' . htmlspecialchars($child['title'] ?? $child['name'] ?? 'Submenu Item') . '</a>';
                $html .= '</li>';
            }
            $html .= '</ul></div></li>';
        } else {
            // 非目录类型或无children的菜单
            $html .= '<li class="' . $active . '">';
            $html .= '<a href="' . tenant_url($item_path) . '" class="'.$active.'" >'.htmlspecialchars($item['title'] ?? $item['name'] ?? 'Menu Item').($is_dir ? '<span uk-icon="icon: chevron-down; ratio: 0.7" class="uk-margin-remove"></span>' : '').'</a>';
            $html .= '</li>';
        }
    }
    $html .= '</ul>';
    return $html;
}

/**
 * 渲染附菜单
 * @param array $menu_data 菜单数据
 * @return string HTML代码
 */
function render_sub_menu($menu_data) {
    global $seg;
    // 检查$seg数组是否有索引1，没有则使用空字符串
    $sub_menu_data = find_sub_menu_data($menu_data, $seg[1] ?? '');

    if (empty($sub_menu_data)) {
        return '';
    }
    
    // 渲染附菜单
    $html = '';
    $html .= '<ul class="uk-navbar-nav">';
    
    // 遍历匹配的菜单项 
    foreach ($sub_menu_data as $sub_key => $sub_item) {
        // 跳过隐藏菜单项
        if (isset($sub_item['hidden']) && $sub_item['hidden'] === true) {
            continue;
        }
        
        $html .= '<li>';
        $item_path = isset($sub_item['mod']) ? $sub_item['mod'] : $sub_key;
        $full_path = $seg[1] . '/' . $item_path;
        $url = tenant_url($full_path);
        $html .= '<a href="' . $url . '">' . htmlspecialchars($sub_item['title'] ?? '') . '</a>';
        $html .= '</li>';
    }
    $html .= '</ul>';    
    return $html;
}

/**
 * 查找附菜单数据
 * @param array $menu_data 菜单数据
 * @param array $path_parts 路径部分
 * @return array 附菜单数据
 */
function find_sub_menu_data($menu_data, $path_parts) {
    if (!isset($path_parts)) {
        return [];
    }
    
    $first_part = $path_parts;
    
    // 查找匹配的顶级菜单项
    foreach ($menu_data as $key => $item) {
        // 跳过隐藏菜单项
        if (isset($item['hidden']) && $item['hidden'] === true) {
            continue;
        }
        
        // 检查是否为目录类型
        $is_dir = isset($item['type']) && $item['type'] === 'dir';
        
        if ($is_dir) {
            // 如果是目录类型，检查其子菜单
            if (isset($item['children']) && is_array($item['children'])) {
                foreach ($item['children'] as $sub_key => $sub_item) {
                    // 跳过隐藏菜单项
                    if (isset($sub_item['hidden']) && $sub_item['hidden'] === true) {
                        continue;
                    }
                    
                    $item_mod = isset($sub_item['mod']) ? $sub_item['mod'] : $sub_key;
                    
                    if ($item_mod === $first_part) {
                        return isset($sub_item['children'])?$sub_item['children']:'';
                    }
                }
            }
        } else {
            // 非目录类型直接匹配
            $item_mod = isset($item['mod']) ? $item['mod'] : $key;
            
            if ($item_mod === $first_part) {
                return  $item['children'];
            }
        }
    }
    
    return [];
}

/**
 * 生成包含租户ID的URL
 * @param string $path 路径
 * @return string 完整URL
 */
function tenant_url($path = '') {
  $path = (string)$path;
  // 确保路径以/开头
  if ($path && $path[0] !== '/') {
    $path = '/' . $path;
  }
  
  // 获取当前租户ID
  $tenant_id = '';
  if (isset($_SESSION['route_tenant_id'])) {
    $tenant_id = $_SESSION['route_tenant_id'];
  } elseif (isset($_SESSION['current_tenant'])) {
    $tenant_id = $_SESSION['current_tenant'];
  }
  
  // 如果有租户ID，则在路径前加上租户ID
  if ($tenant_id) {
    return '/' . $tenant_id . $path;
  }
  
  // 如果没有租户ID，直接返回路径
  return $path;
}

/**
 * Check if current request URI matches the given path
 * @param string $path
 * @return bool
 */
function is_current_path($path) {
    if (empty($path)) return false;
    $current_uri = $_SERVER['REQUEST_URI'] ?? '';
    $current_path = parse_url($current_uri, PHP_URL_PATH) ?? '';
    // Normalize paths by removing trailing slashes
    $normalized_path = rtrim($path, '/');
    $normalized_current = rtrim($current_path, '/');
    return $normalized_path === $normalized_current;
}

/**
 * 渲染对象菜单
 * @param array $menu_data 菜单数据
 * @param string $object_id 对象ID
 * @param string $menu_key 菜单键名
 * @return string HTML代码
 */
function render_object_menu($menu_data, $object_id, $menu_key) {
    $html = '';
    // 检查菜单数据中是否存在指定的菜单键
    if (!isset($menu_data[$menu_key])) {
        return $html;
    }
    
    $menu_item = $menu_data[$menu_key];
    // 检查是否有view子菜单
    if (!isset($menu_item['children']['view']) || empty($menu_item['children']['view']['children'])) {
        return $html;
    }
    
    // 获取租户ID
    $tenant_id = '';
    if (isset($_SESSION['route_tenant_id'])) {
        $tenant_id = $_SESSION['route_tenant_id'];
    } elseif (isset($_SESSION['current_tenant'])) {
        $tenant_id = $_SESSION['current_tenant'];
    }
    
    // model名称即菜单键
    $model = $menu_key;
    
    $view_children = $menu_item['children']['view']['children'];
    $html .= '<ul class="uk-tab" data-uk-tab>';
    foreach ($view_children as $key => $item) {
        // 跳过隐藏菜单项
        if (isset($item['hidden']) && $item['hidden'] === true) {
            continue;
        }
        // 二级操作
        $secondary_operation = $key;
        // 构建新格式的URL: tenantid/model/view/secondary_operation/objectid
        $url_parts = [];
        $url_parts[] = $model;
        $url_parts[] = 'view';
        $url_parts[] = $secondary_operation;
        $url_parts[] = $object_id;
        
        $url_path = implode('/', $url_parts);
        $url = tenant_url($url_path);
        
        // 检查是否为当前活动页面
        $active = is_current_path(parse_url($url, PHP_URL_PATH)) ? 'uk-active' : '';
        $html .= '<li class="' . $active . '">';
        $html .= '<a href="' . $url . '">' . htmlspecialchars($item['title'] ?? '') . '</a>';
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}