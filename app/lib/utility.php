<?php
// function : verify the validity of form
function validate($conf = array(), $data = array()) {
  $data = empty($data) ? $_POST : $data;
  $err = array();
  if(empty($data) || empty($conf)) return $err;
  
  foreach($conf as $key => $val) {
    $rules = explode('|', $val);
    foreach($rules as $rule) {
      switch ($rule) {
        case 'required':
          if(!isset($data[$key]) || empty($data[$key])) {
            $err[$key] = "不能为空";
          }
          break;
        case 'email':
          if(!filter_var($data[$key], FILTER_VALIDATE_EMAIL)) {
            $err[$key] = "email地址错误";
          }
          break;
        default:
          if(!function_exists($rule)) {
            exit('Validate function '.$rule.' does not exist');
          }
          $res = $rule($data[$key]);
          if($res !== TRUE) {
            $err[$key] = $res;
          }
      }
      if(isset($err[$key])) break;
    }
  }
  
  return empty($err) ? TRUE : $err;
}

// function : redirect 
function redirect($url , $msg ='',$ext_msg = '') //跳转
{
  $param = array('url'=>$url, 'msg'=>$msg, 'ext_msg'=>$ext_msg);
 // if(!view('redirect',$param)) header("location:$url");
  view('v/redirect',$param);
  exit();
}

// 分页函数
function pagination($tot , $cur = 1, $psize = 30 , $base_url = '')
{
  $cur = $cur < 1?1:$cur;
  $tot_page = ceil($tot / $psize);
  if($tot_page <= 1 )return; //no pagin
    $result= ' <div class="pagination" >';
    $startp = intval(($cur-1)/10);
    if($tot_page >($startp+1)*10 ) {
      $total=($startp+1)*10;
    }
  else {
    $total=$tot_page;
  }

  if( $cur > 10 ) {
    $result.="[<A HREF=\"".$base_url."1\" >第一页</A>][<A HREF='".$base_url.($startp*10-9)."'>上10页</A>] ";
  }

  for($i=$startp*10+1;$i<=$total;$i++)
  {
    if( $cur == $i )$result.=' <span class="cur" >'.$i.'</span> ';
    else $result.=' <A href="'.$base_url.$i.'">'.$i.'</A> ';
  }

  if( $tot_page>($startp*10+10) )
    $result.=' [ <A HREF="'.$base_url.($startp*10+11).'" > 下10页 </A> ]';
    $result.= ' </div>';
    return $result;
}

// 生成 select options
function select_option($list,$value = FALSE ) // 利用数组 生成 selecct 的 Opitons
{
  $out = '';
  foreach($list as $k=>$v)
  {
    $out.="<option value='$k'".($k==$value?'selected ':'').">$v</option>\n"; 
  }
  return $out;
}

// 显示更加友好的日期
function __time($then) // 格式化时间 例如 ： 10分钟钱
{
  $now=time();
  $time=intval(($now-$then)/60);
  if($time>0) {
    if($time < 60 )return "$time 分钟前";
    else
    {
      $time=intval($time/60);
      if($time<12)return "$time 小时前";
    }
  }
  return date('Y-m-d',$then);
}

// 生成任意字符串用于数据Id，不能重复
function randstr($n = 6) // 生成随机字符串
{
  $str = '0123456789abcdefghijklmnopqrstuvwxyz';
  $s = '';
  $len = strlen($str);
  
  // 使用更安全的随机数生成函数 random_int 替代 rand
  for($i = 0; $i < $n; $i++){
    $s .= $str[random_int(0, $len - 1)];
  }
  
  return $s;
}
// 切断 utf8 代码辅助
function utf8_trim($str) // 用于 substr utf8 时截去最后的乱码 
{
  $hex = '';
  $len = strlen($str);
  for ($i=strlen($str)-1; $i>=0; $i-=1){
    $hex .= ord($str[$i]);
    $ch = ord($str[$i]);
    if (($ch & 128)==0) return(substr($str,0,$i));
    if (($ch & 192)==192) return(substr($str,0,$i));
  }
  return($str.$hex);
}

// 验证 email
function is_email($email) {
  return preg_match('|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$|i', $email);
}


// 文件缓存
function cache_start($time = 0 )
{
  global $uri;
  $file = APP.'cache/'.md5($uri);
  if($time && is_file($file) && filemtime($file) > time() - $time )
  {
    $fp = fopen($file, 'rb');
    fpassthru($fp);
    exit;
  }
}

function cache_end()
{
  global $uri;
  $file = APP.'cache/'.md5($uri);
  $buffer = ob_get_contents();
  file_put_contents($file,$buffer);
}

function cache_clean()
{
  $dir = APP.'cache';
  if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
    $str = "rmdir /s/q " .$dir ;
  } else {
    $str = "rm -Rf " . $dir;
  }
  exec($str);
  mkdir($dir);
}

function md5s($str) //short version of md5
{
  return substr(md5($str),8,16);
}

function _encode($arr)
{
  return json_encode($arr);
}

function _decode($str)
{
  return json_decode($str,true);
}

/**
 * 递归渲染多级菜单
 * @param array $menu_data 菜单数据
 * @param int $level 菜单层级
 * @return string HTML代码
 */
function render_menu($menu_data, $level = 0) {
    $html = '';
    
    // 根据层级设置不同的CSS类
    $ul_class = $level == 0 ? 'nav nav-pills' : 'submenu';
    $li_class = $level == 0 ? '' : 'submenu-item';
    
    $html .= '<ul class="' . $ul_class . '">';
    
    foreach ($menu_data as $key => $item) {
        // 检查是否有子菜单
        $has_children = isset($item['children']) && is_array($item['children']) && !empty($item['children']);
        // 为有子菜单的项添加特殊类
        $item_class = $has_children ? 'has-children' : '';
        $html .= '<li class="' . $li_class . ' ' . $item_class . '">';
        $url =  tenant_url(isset($item['mod']) ? $item['mod'] : $key);
        $html .= '<a href="' .$url . '">' . htmlspecialchars($item['title'] ?? '') . '</a>';
        
        // 如果有子菜单，递归渲染
        if ($has_children) {
            $html .= render_menu($item['children'], $level + 1);
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    
    return $html;
}

/**
 * 转换菜单数据格式
 * @param array $menu_data 原始菜单数据
 * @return array 转换后的菜单数据
 */
function convert_menu_data($menu_data) {
    $result = array();
    
    foreach ($menu_data as $key => $item) {
        // 跳过隐藏的菜单项
        if (isset($item['hidden']) && $item['hidden'] === true) {
            continue;
        }
        
        $new_item = array(
            'name' => isset($item['title']) ? $item['title'] : $key,
            'url' => isset($item['url']) ? $item['url'] : '#'
        );
        
        // 处理子菜单
        if (isset($item['children']) && is_array($item['children'])) {
            $new_item['children'] = convert_menu_data($item['children']);
        }
        
        $result[] = $new_item;
    }
    
    return $result;
}

/**
 * 生成包含租户ID的URL
 * @param string $path 路径
 * @return string 完整URL
 */
function tenant_url($path = '') {
  // 确保路径以/开头
  if ($path && $path[0] !== '/') {
    $path = '/' . $path;
  }
  
  // 获取当前租户ID
  $tenant_id = '';
  if (isset($_SESSION['route_tenant_id'])) {
    $tenant_id = $_SESSION['route_tenant_id'];
  } elseif (isset($_SESSION['current_tenant'])) {
    $tenant_id = $_SESSION['current_tenant'];
  }
  
  // 如果有租户ID，则在路径前加上租户ID
  if ($tenant_id) {
    return '/' . $tenant_id . $path;
  }
  
  // 如果没有租户ID，直接返回路径
  return $path;
}
