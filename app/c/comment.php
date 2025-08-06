<?php
class comment extends base {
  protected $m;
  
  public function __construct() {
    parent::__construct();
    $this->m = load('m/comment_m');
  }

  /**
   * 评论列表页面
   */
  public function index(): void {
    $pageId = $_GET['page_id'] ?? '';
    if (empty($pageId)) {
      show_404('页面ID不能为空');
    }
    
    $page = $_GET['page'] ?? 1;
    $limit = 10;
    $comments = $this->m->getCommentsByPageId($pageId, $page, $limit);
    $count = $this->m->getCommentCount($pageId);
    
    $param['comments'] = $comments;
    $param['pageId'] = $pageId;
    $param['page_count'] = ceil($count / $limit);
    $param['current_page'] = $page;
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '评论列表';
    $this->display('v/comment/list', $param);
  }

  /**
   * 创建评论 (AJAX)
   */
  public function create(): void {
    // 检查是否为AJAX请求
    if (!is_ajax()) {
      show_404();
    }
    
    // 检查用户是否登录
    $user = $this->check();
    if (!$user) {
      json_output(array('code' => 1, 'msg' => '请先登录'));
    }
    
    // 验证数据
    $conf = array('content' => 'required', 'page_id' => 'required');
    $err = validate($conf);
    
    if ($err === TRUE) {
      $data = array(
        'content' => $_POST['content'],
        'user_id' => $user['id'],
        'page_id' => $_POST['page_id']
      );
      
      $result = $this->m->createComment($data);
      if ($result) {
        json_output(array('code' => 0, 'msg' => '评论成功', 'data' => array('comment_id' => $result)));
      } else {
        json_output(array('code' => 1, 'msg' => '评论失败'));
      }
    } else {
      json_output(array('code' => 1, 'msg' => is_array($err) ? reset($err) : '数据验证失败'));
    }
  }

  /**
   * 删除评论
   */
  public function delete(): void {
    // 检查用户是否登录
    $user = $this->check();
    if (!$user) {
      redirect(tenant_url('user/login/'), '请先登录');
    }
    
    $id = $_GET['id'];
    $result = $this->m->deleteComment($id);
    
    if ($result) {
      redirect(tenant_url('comment/'), '评论删除成功');
    } else {
      redirect(tenant_url('comment/'), '评论删除失败');
    }
  }

  /**
   * 获取评论 (AJAX)
   */
  public function getComments(): void {
    // 检查是否为AJAX请求
    if (!is_ajax()) {
      show_404();
    }
    
    $pageId = $_GET['page_id'] ?? '';
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    
    if (empty($pageId)) {
      json_output(array('code' => 1, 'msg' => '页面ID不能为空'));
    }
    
    $comments = $this->m->getCommentsByPageId($pageId, $page, $limit);
    $count = $this->m->getCommentCount($pageId);
    
    json_output(array(
      'code' => 0,
      'msg' => '获取成功',
      'data' => array(
        'comments' => $comments,
        'count' => $count,
        'page_count' => ceil($count / $limit),
        'current_page' => $page
      )
    ));
  }
}
?>