<?php
class user_m extends m {
	public $table;
	public $fields;
	protected $auth;
	protected $login_err;

	public function __construct() {
		parent::__construct('tb_user');
		$this->fields = array('id', 'email','username','password');
		$this->auth = 'auth';
		$this->login_err = '';
	}

	/**
	 * 获取登录错误信息
	 * @return string
	 */
	public function getLoginError() {
		return $this->login_err;
	}

    /**
     * 获取用户列表
     * @param int $page 页码
     * @param int $limit 每页记录数
     * @return array
     */
    public function userlist($page = 1, $limit = 20) {
        return $this->getPage($page, $limit);
    }
    
    /**
     * 根据ID获取用户信息
     * @param string $id 用户ID
     * @return array|null
     */
    public function getUser($id) {
        return $this->getOne($id);
    }

    /**
     * 创建用户
     * @param array $data 用户数据
     * @return bool
     */
    public function createUser($data) {
        // 加密密码
        $data['password'] = $this->encode($data['password']);
        return $this->add($data);
    }
    
    /**
     * 更新用户
     * @param string $id 用户ID
     * @param array $data 用户数据
     * @return bool
     */
    public function updateUser($id, $data) {
        // 加密密码
        $data['password'] = $this->encode($data['password']);
        return $this->update($id, $data);
    }
    
    /**
     * 删除用户
     * @param string $id 用户ID
     * @return bool
     */
    public function deleteUser($id) {
        return $this->del($id);
    }

    function login($username,$password){
	    $username = $this->db->escape($username);
	    // 先检查表结构
	    $hasLevel = false;
	    if($this->db->driver == 'sqlite') {
	      $tableInfo = $this->db->query("PRAGMA table_info(tb_user)");
	      foreach($tableInfo as $col) {
	        if($col['name'] == 'level') {
	          $hasLevel = true;
	          break;
	        }
	      }
	    }
	    
	    // 根据表结构构建查询
	    $query = "SELECT id, username, email, password" . 
	            ($hasLevel ? ", level" : "") . 
	            " FROM tb_user WHERE LOWER(username)=lower('$username') LIMIT 1";
	    $user = $this->db->query($query);
	    
	    if(!isset($user[0])){
	      $this->login_err = '用户不存在！';
	      return FALSE;
	    }
	    
	    if($user[0]['password'] != $this->encode($password)) {
	      $this->login_err = '密码错误！';
	      return FALSE;
	    }
	    
	    $auth = array(
	      'id'    => $user[0]['id'],
	      'name'  => $user[0]['username'],
	      'email' => $user[0]['email'],
	      'level' => isset($user[0]['level']) ? $user[0]['level'] : 0,
	      'seed'  => md5(SEED.$user[0]['id'].(isset($user[0]['level']) ? $user[0]['level'] : 0))
	    );
	    
	    $value = serialize($auth);
	    setcookie($this->auth, $value, time()+360000,"/");
	    return TRUE;
    }
    
    function isexist($name){
		$query = "SELECT COUNT(*) as count FROM ".$this->table." WHERE username='".$this->db->escape($name)."'";
		$res=$this->db->query($query);
		if($res[0]['count'] > 0){
			return '用户名已存在';
		}else {
			return true;
		}
	}
    
	  function check()
	  {
	    if(isset($_COOKIE[$this->auth])){
	      $u = unserialize($_COOKIE[$this->auth]);
	      if(isset($u)){
	        return $u;
	      }
	    }
	    return array(
	      'id' => 0,
	      'level' => 0,
	      'name' => '',
	      'email' => '',
	      'seed' => ''
	    );
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
