<?php
class user extends base{
  protected $m;
  public function __construct(){
    parent::__construct();
    $this->m=load('m/user_m');
  }
  
  /**
   * 用户列表页面
   */
  public function index(): void {
    $users = $this->m->userlist();
    $param['users'] = $users;
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '用户列表';
    $this->display('v/user/userlist', $param);
  }
  
  /**
   * 创建用户页面
   */
  public function create(): void {
    $conf = array('email'=>'required|email','username'=>'required|isexist','password'=>'required');
    $err = validate($conf);
    
    if(!empty($_POST) && (!isset($_POST['password']) || !isset($_POST['confirm_password']) || $_POST['password'] != $_POST['confirm_password'])) {
      $err['confirm_password']='两次密码不一样';
    } elseif(!empty($_POST) && $err === TRUE) {
      // 在控制器中生成ID并添加到数据中
      $_POST['id'] = randstr(8);
      $result = $this->m->createUser($_POST);
      if ($result) {
        redirect(tenant_url('user/'), '用户创建成功。');
      } else {
        $err = array('error' => '创建用户失败');
      }
    }
    
    $param['val'] = $_POST;
    $param['err'] = is_array($err) ? $err : array();
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '创建用户';
    $this->display('v/user/create',$param);
  }
  
  /**
   * 编辑用户页面
   */
  public function edit(): void {
    $id = $_GET['id'];
    $user = $this->m->getUser($id);
    
    if (!$user) {
      show_404('用户不存在');
    }
    
    $conf = array('email'=>'required|email','username'=>'required|isexist','password'=>'required');
    $err = validate($conf);
    
    if(!empty($_POST) && (!isset($_POST['password']) || !isset($_POST['confirm_password']) || $_POST['password'] != $_POST['confirm_password'])) {
      $err['confirm_password']='两次密码不一样';
    } elseif(!empty($_POST) && $err === TRUE) {
      $result = $this->m->updateUser($id, $_POST);
      if ($result) {
        redirect(tenant_url('user/'), '用户更新成功。');
      } else {
        $err = array('error' => '更新用户失败');
      }
    }
    
    $param['user'] = $user;
    $param['val'] = $_POST;
    $param['err'] = is_array($err) ? $err : array();
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '编辑用户';
    $this->display('v/user/edit', $param);
  }
  
  /**
   * 删除用户
   */
  public function delete(): void {
    $id = $_GET['id'];
    $result = $this->m->deleteUser($id);
    
    if ($result) {
      redirect(tenant_url('user/'), '用户删除成功。');
    } else {
      redirect(tenant_url('user/'), '删除用户失败。');
    }
  }
  
  function login(){ 
      $conf = array('username'=>'required','password'=>'required');
      $err = validate($conf);
      
      $param = array(
        'page_title' => '登录',
        'meta_keywords' => '登录',
        'meta_description' => '登录',
        'val' => isset($_POST) ? $_POST : array(),
        'err' => is_array($err) ? $err : array()
      );

      if(!is_array($err) && $this->m->login($_POST['username'], $_POST['password'])) {
        redirect(tenant_url(''), '登录成功。');
        exit;
      } else {
        $param['info'] = $this->m->getLoginError();
        $this->display('v/user/login',$param);   
        exit;
      }
  }
  
  function logout(){
  	$this->m->logout();
    redirect(BASE,'退出登录！');
  }
  
  /**
   * 用户注册页面
   */
  public function reg(): void {
    $conf = array('email'=>'required|email','username'=>'required','password'=>'required');
    $err = validate($conf);
    
    if(!empty($_POST) && (!isset($_POST['password']) || !isset($_POST['confirm_password']) || $_POST['password'] != $_POST['confirm_password'])) {
      $err['confirm_password']='两次密码不一样';
    } elseif(!empty($_POST) && $err === TRUE) {
      // 检查用户名是否已存在
      $existResult = $this->m->isexist($_POST['username']);
      if($existResult !== true) {
        $err['username'] = $existResult;
      } else {
        // 在控制器中生成ID并添加到数据中
        $_POST['id'] = randstr(8);
        $result = $this->m->createUser($_POST);
        if ($result) {
          redirect(tenant_url('user/login/'), '注册成功，请登录。');
        } else {
          $err = array('error' => '注册失败');
        }
      }
    }
    
    $param['val'] = $_POST;
    $param['err'] = is_array($err) ? $err : array();
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '用户注册';
    $this->display('v/user/register',$param);
  }
  
  function test(){
    
  }
}
