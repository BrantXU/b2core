<?php
class user extends base{
  protected user_m $m;
  public function __construct(){
    parent::__construct();
    $this->m=load('m/user_m');
  }
  
  public function reg(): void {
      $conf = ['email' => 'required|email', 'username' => 'required|isexist', 'password' => 'required'];
      $err = validate($conf);
      if(!empty($_POST) && (!isset($_POST['password']) || !isset($_POST['repassword']) || $_POST['password'] != $_POST['repassword'])) {
        $err['password']='两次密码不一样';
        $param['val'] = $_POST ?? [];
        $param['err'] = $err;
        $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '注册';
        $this->display('v/user/register',$param);
      } elseif(!empty($_POST) && $err === TRUE) {
          $this->m->register();
          redirect(BASE,'注册成功，请登录。');
      }else {
          $param['val'] = $_POST;
          $param['err'] = $err;
          $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '注册';
          $this->display('v/user/register',$param);    
      }  
  }
  
  public function login(): void { 
      $conf = ['username' => 'required', 'password' => 'required'];
      $err = validate($conf);
      
      $param = [
        'page_title' => '登录',
        'meta_keywords' => '登录',
        'meta_description' => '登录',
        'val' => $_POST ?? [],
        'err' => is_array($err) ? $err : []
      ];

      if(!is_array($err) && $this->m->login($_POST['username'], $_POST['password'])) {
        redirect(BASE,'登录成功。');
        exit;
      } else {
        $param['info'] = $this->m->getLoginError();
        $this->display('v/user/login',$param);   
        exit;
      }
  }
  
  public function userlist(): void {
    $user=$this->m->userlist();
    $param['user']=$user;
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '用户列表';
    $this->display('v/user/userlist',$param);
  }
  public function userdelete(): void {
    $id = (int)($_GET['id'] ?? 0);
    $this->m->userdelete($id);
    $user=$this->m->userlist();
    $param['user']=$user;
    $this->display('v/user/userlist',$param);
  }
  
  public function update(): void {
      $conf = ['email' => 'required|email', 'username' => 'required|isexist', 'password' => 'required'];
      $err = validate($conf);
      $user['id'] = (int)($_GET['id'] ?? 0);
      $param['user']=$user;
      if(!empty($_POST) && (!isset($_POST['password']) || !isset($_POST['repassword']) || $_POST['password'] != $_POST['repassword'])) {
        $err['password']='两次密码不一样';
        $param['val'] = $_POST ?? [];
        $param['err'] = $err;
        $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '更新';
        $this->display('v/user/update',$param); 
      } elseif(!empty($_POST) && $err === TRUE) {
          $this->m->userupdate($_POST['id'],$_POST);
          redirect(BASE,'修改成功，请登录。');
      }else {
          $param['val'] = $_POST;
          $param['err'] = $err;
          $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '更新';
          $this->display('v/user/update',$param);    
      }  
  }
  
  public function logout(): void {
  	$this->m->logout();
    redirect(BASE,'退出登录！');
  }
  
  public function test(): void {
    
  }
}
