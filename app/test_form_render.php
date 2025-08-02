<?php
/**
 * 测试表单渲染功能
 */

// 引入必要的文件
require_once dirname(__FILE__) . '/lib/form_render.php';
require_once dirname(__FILE__) . '/lib/db.php';
require_once dirname(__FILE__) . '/m/entity_m.php';

// 初始化数据库连接
$db_config = array(
    'driver' => 'sqlite',
    'sqlite' => array(
        'database' => dirname(__FILE__) . '/db.sqlite',
    )
);
$db = new db($db_config);
$GLOBALS['db_tenant'] = $db;

// 调试：直接查询entity表查看数据
$entityModel = new entity_m();
$fundEntities = $entityModel->getAllEntities('Fund');
echo '<pre>查询到的Fund实体数据: ' . print_r($fundEntities, true) . '</pre>';

// 模拟表单配置
$fieldConfig = array(
    'id' => 'investedFunds',
    'name' => '参投基金',
    'listed' => 1,
    'width' => 3,
    'label_width' => 1,
    'type' => 'select_new',
    'readonly' => 0,
    'props' => array(
        'type' => 'mod',
        'data_source' => 'Fund',
        'multiple' => '是'
    )
);

// 模拟实体数据
$entityData = array();
$val = array();
$err = array();
$view = false;

// 渲染表单字段
$html = FormRenderer::renderFormField('investedFunds', $fieldConfig, $entityData, $val, $err, $view);

// 输出结果
echo '<!DOCTYPE html>
<html>
<head>
    <title>表单渲染测试</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.16.19/dist/css/uikit.min.css" />
</head>
<body>
    <div class="uk-container uk-margin-top">
        <h1>测试 select_new 类型 (props.type=mod)</h1>
        <div class="uk-card uk-card-default uk-card-body">
            ' . $html . '
        </div>
        <div class="uk-margin-top">
            <h3>生成的HTML代码:</h3>
            <pre>' . htmlspecialchars($html) . '</pre>
        </div>
    </div>
</body>
</html>';