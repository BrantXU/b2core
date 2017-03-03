<?php
class user extends base{
  var $m;
  function __construct(){
    parent::__construct();
    $this->m=load('m/user_m');
  }
  
  function reg(){
      $conf = array('email'=>'required|email','username'=>'required|isexist','password'=>'required');
      $err = validate($conf);
      if($_POST['password']!=$_POST['repassword']){
        $err['password']='两次密码不一样';
        $param['val'] = $_POST;
        $param['err'] = $err;
        $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '注册';
        $this->display('v/user/register',$param);
      }elseif( $err === TRUE) {
          $this->m->register();
          redirect(BASE,'注册成功，请登录。');
      }else {
          $param['val'] = $_POST;
          $param['err'] = $err;
          $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '注册';
          $this->display('v/user/register',$param);    
      }  
  }
  
  function login(){ 
      $conf = array('username'=>'required','password'=>'required');
      $err = validate($conf);

      
      if( !is_array($err)&&$this->m->login( $_POST['username'] , $_POST['password'] )){
        redirect(BASE,'登录成功。');
        exit;
      }else {
        $param['info']= $this->m->login_err;
        $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '登录';
        $this->display('v/user/login',$param);   
        exit;
      }
  }
  
  function userlist(){
    $user=$this->m->userlist();
    $param['user']=$user;
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '用户列表';
    $this->display('v/user/userlist',$param);
  }
  function userdelete(){
    $id=$_GET['id'];
    $this->m->userdelete($id);
    $user=$this->m->userlist();
    $param['user']=$user;
    $this->display('v/user/userlist',$param);
  }
  
  function update(){
      $conf = array('email'=>'required|email','username'=>'required|isexist','password'=>'required');
      $err = validate($conf);
      $user['id']=$_GET['id'];
      $param['user']=$user;
      if($_POST['password']!=$_POST['repassword']){
        $err['password']='两次密码不一样';
        $param['val'] = $_POST;
        $param['err'] = $err;
        $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '更新';
        $this->display('v/user/update',$param); 
      }elseif( $err === TRUE) {
          $this->m->userupdate($_POST['id'],$_POST);
          redirect(BASE,'修改成功，请登录。');
         }else {
          $param['val'] = $_POST;
          $param['err'] = $err;
          $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '更新';
          $this->display('v/user/update',$param);    
         }  
  }
  
  function logout(){
  	$this->m->logout();
    redirect(BASE,'退出登录！');
  }
  
  function test(){
    
  }
}
