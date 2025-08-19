<?php
class home extends base{
    function __construct()
    {
    	parent::__construct();
    }
    public function index():void {

        $this->log($_SESSION);
        $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = 'welcome';
        $this->display('v/index', $param);
    }

    /**
     * 评论模块测试页面
     */
    public function test_comment(): void {
        $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '评论模块测试';
        $this->display('v/test_comment', $param);
    }
}
?>