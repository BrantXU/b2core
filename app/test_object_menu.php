<?php
/**
 * 测试对象菜单渲染功能
 */

// 加载必要的文件和函数
require_once __DIR__ . '/lib/menu_render.php';
require_once __DIR__ . '/lib/yaml.php';
require_once __DIR__ . '/lib/utility.php';

// 模拟会话数据
session_start();
$_SESSION['current_tenant'] = 'default';

// 加载菜单数据
$menu_path = __DIR__ . '/../data/default/men/k9fqwjqn.yaml';
// 使用YAML类的decode方法加载YAML文件
$menu_data = YAML::decode(file_get_contents($menu_path));

// 测试对象菜单渲染
$object_id = 'test_object_id'; // 测试用的对象ID
$menu_key = '1erztlh1'; // 基金管理菜单的键名

// 渲染对象菜单
$object_menu_html = render_object_menu($menu_data, $object_id, $menu_key);

// 输出结果
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>对象菜单测试</title>
    <link rel="stylesheet" href="/uikit.min.css">
    <script src="/uikit.min.js"></script>
    <script src="/uikit-icons.min.js"></script>
</head>
<body class="uk-background-muted">
    <div class="uk-container uk-padding">
        <h1 class="uk-heading-medium">对象菜单测试</h1>
        
        <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
            <h3 class="uk-card-title">测试参数</h3>
            <p>对象ID: <?php echo htmlspecialchars($object_id); ?></p>
            <p>菜单键名: <?php echo htmlspecialchars($menu_key); ?></p>
        </div>
        
        <h2 class="uk-heading-small">渲染结果</h2>
        <?php echo $object_menu_html; ?>
    </div>
</body>
</html>