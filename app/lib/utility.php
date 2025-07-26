<?php
// function : verify the validity of form
function validate(array $conf = [], array $data = []): array|bool {
  $data = empty($data) ? ($_POST ?? []) : $data;
  $err = [];
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
          if(!filter_var($data[$key] ?? '', FILTER_VALIDATE_EMAIL)) {
            $err[$key] = "email地址错误";
          }
          break;
        default:
          if(!function_exists($rule)) {
            exit('Validate function '.$rule.' does not exist');
          }
          $res = $rule($data[$key] ?? '');
          if($res !== TRUE) {
            $err[$key] = $res;
          }
      }
      if(isset($err[$key])) break;
    }
  }
  
  return empty($err) ? true : $err;
}

// function : redirect 
function redirect(string $url, string $msg = '', string $ext_msg = ''): void //跳转
{
  $param = ['url' => $url, 'msg' => $msg, 'ext_msg' => $ext_msg];
  view('v/redirect', $param);
  exit();
}

// 分页函数
function pagination(int $tot, int $cur = 1, int $psize = 30, string $base_url = ''): ?string
{
  $cur = $cur < 1 ? 1 : $cur;
  $tot_page = (int)ceil($tot / $psize);
  if($tot_page <= 1) return ''; //no pagination
    $result = ' <div class="pagination" >';
    $startp = (int)(($cur - 1) / 10);
    $total = $tot_page > ($startp + 1) * 10 ? ($startp + 1) * 10 : $tot_page;

  if($cur > 10) {
    $result .= "[<A HREF=\"{$base_url}1\" >第一页</A>][<A HREF='{$base_url}".($startp*10-9)."'>上10页</A>] ";
  }

  for($i = $startp * 10 + 1; $i <= $total; $i++)
  {
    if($cur == $i) {
      $result .= ' <span class="cur" >'.$i.'</span> ';
    } else {
      $result .= ' <A href="{$base_url}{$i}">{$i}</A> ';
    }
  }

  if($tot_page > ($startp * 10 + 10)) {
    $result .= ' [ <A HREF="{$base_url}".($startp*10+11)."" > 下10页 </A> ]';
  }
    $result .= ' </div>';
    return $result;
}

// 生成 select options
function select_option(array $list, mixed $value = false): string // 利用数组 生成 select 的 Options
{
  $out = '';
  foreach($list as $k => $v)
  {
    $selected = $k == $value ? 'selected ' : '';
    $out .= "<option value='{$k}'{$selected}>{$v}</option>\n";
  }
  return $out;
}

// 显示更加友好的日期
function __time(int $then): string // 格式化时间 例如：10分钟前
{
  $now = time();
  $time = (int)(($now - $then) / 60);
  if($time > 0) {
    if($time < 60) {
      return "{$time} 分钟前";
    } else {
      $time = (int)($time / 60);
      if($time < 12) {
        return "{$time} 小时前";
      }
    }
  }
  return date('Y-m-d', $then);
}

// 生成任意字符串
function randstr(int $n = 8): string // 生成随机字符串
{
  $str = '0123456789abcdefghijklmnopqrstuvwxyz';
  $s = '';
  $len = strlen($str) - 1;
  for($i = 0; $i < $n; $i++){
    $s .= $str[rand(0, $len)];
  }
  return $s;
}

// 切断 utf8 代码辅助
function utf8_trim(string $str): string // 用于 substr utf8 时截去最后的乱码 
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
function is_email(string $email): bool {
  return (bool)preg_match('|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$|i', $email);
}


// 文件缓存
function cache_start(int $time = 0): void
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

function cache_end(): void
{
  global $uri;
  $file = APP.'cache/'.md5($uri);
  $buffer = ob_get_contents();
  file_put_contents($file,$buffer);
}

function cache_clean(): void
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

function md5s(string $str): string //short version of md5
{
  return substr(md5($str),8,16);
}

function _encode(mixed $arr): string|false
{
  return json_encode($arr);
}

function _decode(string $str): mixed
{
  return json_decode($str,true);
}
