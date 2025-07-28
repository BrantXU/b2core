<?php
class test extends base {
    public function index(): void {
        $param['page_title'] = '测试页面';
        $param['tenant_id'] = isset($_SESSION['route_tenant_id']) ? $_SESSION['route_tenant_id'] : '未设置';
        $this->display('v/test', $param);
    }
    
    public function hello(): void {
        echo "Hello from test controller";
    }
}