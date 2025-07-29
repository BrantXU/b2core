<?php
// 直接测试菜单渲染功能

// 设置基本常量
define('APP', __DIR__ . '/');
define('BASE', '');

// 仅加载需要的函数
require_once APP . 'lib/utility.php';

// 测试菜单数据
// 从JSON文件加载菜单数据
$menu_json = file_get_contents('../data/default/menu.json');
$menu_data = json_decode($menu_json, true);

// 如果JSON解析失败，使用默认测试数据
if ($menu_data === null) {
      $menu_data = array(
          array(
              'name' => '首页',
              'url' => '/home'
          ),
          array(
              'name' => '任务',
              'url' => '/task',
              'children' => array(
                  array(
                      'name' => '我的任务',
                      'url' => '/task/mine'
                  ),
                  array(
                      'name' => '任务管理',
                      'url' => '#',
                      'children' => array()
                  )
              )
          )
      );
  }
                    array(
                        'name' => '待办任务',
                        'url' => '/task/todo',
                    ),
                    array(
                        'name' => '已完成任务',
                        'url' => '/task/done',
                    ),
                ),
            ),
        ),
    ),
    array(
        'name' => '系统管理',
        'url' => '/admin',
        'children' => array(
            array(
                'name' => '用户管理',
                'url' => '/admin/users',
            ),
            array(
                'name' => '角色管理',
                'url' => '/admin/roles',
            ),
        ),
    ),
);

// 渲染菜单
$html = render_menu($menu_data, 0);

// 输出HTML页面
echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<meta charset="utf-8">';
echo '<title>菜单渲染测试</title>';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; margin: 20px; }';
echo '.nav, .submenu { list-style: none; padding: 0; margin: 0; }';
echo '.nav > li { display: inline-block; position: relative; }';
echo '.submenu { display: none; position: absolute; top: 100%; left: 0; background: #fff; border: 1px solid #ccc; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 1000; }';
echo '.nav > li:hover > .submenu { display: block; }';
echo '.submenu-item { display: block; min-width: 150px; }';
echo '.submenu .submenu { left: 100%; top: 0; }';
echo '.submenu-item > a { display: block; padding: 8px 15px; text-decoration: none; color: #000; }';
echo '.submenu-item > a:hover { background: #f5f5f5; }';
echo '.has-children > a::after { content: " ▼"; font-size: 0.8em; }';
echo '</style>';
echo '</head>';
echo '<body>';
echo '<h1>菜单渲染测试</h1>';
echo $html;
echo '<p>这是一个测试多级菜单渲染的页面。</p>';
echo '</body>';
echo '</html>';