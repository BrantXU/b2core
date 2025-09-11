<?php
// 登录脚本获取session cookie
$loginUrl = 'http://localhost:8081/default/user/login/';
$loginData = [
    'username' => 'admin',
    'password' => 'admin'
];

// 使用curl登录并获取cookie
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($loginData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode === 200) {
    echo "登录成功！获取到session cookie。\n";
    
    // 读取cookie文件
    if (file_exists('/tmp/cookies.txt')) {
        $cookies = file_get_contents('/tmp/cookies.txt');
        echo "Cookie内容:\n" . $cookies . "\n";
    }
} else {
    echo "登录失败，HTTP代码: $httpCode\n";
    echo "响应: $response\n";
}

curl_close($ch);
?>