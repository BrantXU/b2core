<?php
// 测试配置更新API
$url = 'http://localhost:8081/default/config/update';

// 使用从登录获取的session cookie
$cookie = 'PHPSESSID=c418310b86ab915499e499d48fbf77a1';

// 准备测试数据
$data = [
    'config_id' => '12345678',
    'widgets' => [
        'widget1' => [
            'id' => 'widget1',
            'name' => '测试控件',
            'type' => 'text',
            'width' => 2,
            'listed' => true,
            'required' => false,
            'readonly' => false,
            'tips' => '测试提示信息',
            'props' => []
        ]
    ],
    'widget_order' => ['widget1']
];

$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data, JSON_UNESCAPED_UNICODE)
    ]
];

$context  = stream_context_create($options);

// 添加错误处理
$result = @file_get_contents($url, false, $context);

if ($result === FALSE) {
    $error = error_get_last();
    echo "API请求失败: " . $error['message'] . "\n";
    
    // 尝试使用curl作为备选方案
    echo "尝试使用curl...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo "Curl错误: " . curl_error($ch) . "\n";
    } else {
        $response = json_decode($result, true);
        echo "API响应: \n";
        print_r($response);
    }
    
    curl_close($ch);
} else {
    $response = json_decode($result, true);
    echo "API响应: \n";
    print_r($response);
}
?>