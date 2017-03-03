<?php
class user_m extends m{
	function __construct(){
		parent::__construct();
		$this->table = 'tb_user';
	    $this->fields = array('email','username','password');
	    $this->auth = 'auth';
	    $this->login_err = '';
	}
	function register(){
		$query_list = array();
	    if(!$elem)$elem = $_POST;
	    // 验证重复
	    // 加密
	    $elem['password'] = $this->encode($elem['password']);
	    $this->add($elem);
	}
	
    function login($username,$password){
	    $username = addslashes($username);
	    $user = $this->db->query("select * from tb_user where LOWER(username)=lower('$username')");
	    if(!isset($user[0])){
	      $this->login_err = '用户不存在！';
	      return FALSE;
	    }
	    
	    if($user[0]['password'] != $this->encode($password) )
	    {
	      $this->login_err = '密码错误！';
	      return FALSE;
	    }
	    
	    $auth = array(
      'id'    =>  $user[0]['id'],
      'name'=>$user[0]['username'],
      'email'=> $user[0]['email'],
      'seed' => md5(SEED.$user[0]['id'].$user[0]['level'])
       );
       
	    $value = serialize($auth);
	    
	    
      setcookie($this->auth, $value, time()+360000,"/");
	    return TRUE;
    }
    function userlist(){
     	$query="select * from ".$this->table;
    	$elemt=$this->db->query($query);
    	return $elemt;
    }
	function isexist($name){
		$query="select * from ".$this->table." where username="."'".$name."'";
		$res=$this->db->query($query);
		if(count($res)>0){
			return '用户名已存在';
		}else {
			return true;
		}
	}
	
    function userdelete($id){
    	$this->del($id);
    }
    function userupdate($id,$user){
    	$user['password']=$this->encode($user['password']);
    	$this->update($id,$user);
    }
    
	  function check()
	  {
	    if(isset($_COOKIE[$this->auth])){
	      $u = unserialize($_COOKIE[$this->auth]);
	      if(isset($u)){
	        return $u;
	      }
	    }
	    return array('id'=>0,'level'=>0);
	  }
    
	
    private function encode($string){
 			return md5($string);
    }
    
    function logout(){
    	setcookie($this->auth, '', time()-36000,"/"); 
    }
}
/* validate functions */
function isexist($name){
	$user=load('m/user_m');
    $info=$user->isexist($name);
    return  $info;
}
