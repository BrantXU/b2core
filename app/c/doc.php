<?php
class doc extends base {
  public $doc_m;
   
  public function __construct() {
    parent::__construct();
    $this->doc_m = load('m/doc_m');
  }

  /**
   * 文档管理主页面 - 前端渲染
   */
  public function index(): void { 
    $tenant_id = $_SESSION['current_tenant'] ?? 'default';
    $entity_id = $this->seg[1] ?? '';
    
    $param = [
      'tenant_id' => $tenant_id,
      'entity_id' => $entity_id
    ];
    // 使用新的前端界面
    $this->display('v/doc/new_index', $param);
  }

  /**
   * 获取文档列表API
   */
  public function api_list(): void {
    header('Content-Type: application/json');
    
    try {
      $tenant_id = $_SESSION['current_tenant'] ?? 'default';
      $entity_id = $_GET['entity_id'] ?? '';
      $view_type = $_GET['view_type'] ?? 'time'; // time: 时间倒序, dir: 目录视图
      

      
      $docs = $this->doc_m->getDocumentsByEntity($entity_id);
      
      // 根据视图类型处理数据
      if ($view_type === 'dir') {
        $docs = $this->organizeByDirectory($docs);
      }
      
      echo json_encode([
        'success' => true,
        'data' => $docs
      ]);
    } catch (Exception $e) {
      echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    }
  }

  /**
   * 按目录组织文档
   */
  private function organizeByDirectory($docs) {
    $organized = [];
    foreach ($docs as $doc) {
      $path = $doc['filepath'] ?? '';
      $parts = explode('/', trim($path, '/'));
      
      $current = &$organized;
      foreach ($parts as $part) {
        if (!empty($part)) {
          if (!isset($current[$part])) {
            $current[$part] = [];
          }
          $current = &$current[$part];
        }
      }
      $current[] = $doc;
    }
    return $organized;
  }

  /**
   * 创建目录API
   */
  public function api_create_directory(): void {
    header('Content-Type: application/json');
    
    try {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('只支持POST请求');
      }
      
      $tenant_id = $this->seg[0] ?? 'default';
      $entity_id = $_POST['entity_id'] ?? '';
      $directory_name = $_POST['directory_name'] ?? '';
      $parent_path = $_POST['parent_path'] ?? '';
      
      if (empty($directory_name)) {
        throw new Exception('目录名称不能为空');
      }
      
      // 构建完整路径
      $full_path = $parent_path ? $parent_path . '/' . $directory_name : $directory_name;
      
      // 检查目录是否已存在
      $existing_doc = $this->doc_m->getDocumentByUploadPath($entity_id, $full_path);
      if ($existing_doc) {
        throw new Exception('目录已存在');
      }
      
      // 创建目录记录
      $directory_data = [
        'tenant_id' => $tenant_id,
        'entity_id' => $entity_id,
        'name' => $directory_name,
        'filename' => $directory_name,
        'filepath' => $full_path,
        'filetype' => 'directory',
        'filesize' => 0,
        'original_size' => 0,
        'upload_path' => $full_path,
        'description' => '目录',
        'created_by' => $_SESSION['user_id'] ?? 'system',
        'is_directory' => 1
      ];
      
      $result = $this->doc_m->createDocument($directory_data);
      
      if ($result) {
        echo json_encode([
          'success' => true,
          'message' => '目录创建成功',
          'data' => $directory_data
        ]);
      } else {
        throw new Exception('目录创建失败');
      }
    } catch (Exception $e) {
      echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    }
  }

  /**
   * 移动文件/目录API
   */
  public function api_move(): void {
    header('Content-Type: application/json');
    
    try {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('只支持POST请求');
      }
      
      $tenant_id = $this->seg[0] ?? 'default';
      $entity_id = $_POST['entity_id'] ?? '';
      $doc_id = $_POST['doc_id'] ?? '';
      $target_path = $_POST['target_path'] ?? '';
      
      if (empty($doc_id)) {
        throw new Exception('文档ID不能为空');
      }
      
      // 获取文档信息
      $doc = $this->doc_m->getDocumentById($doc_id);
      if (!$doc) {
        throw new Exception('文档不存在');
      }
      
      // 构建新路径
      $new_filename = $doc['filename'];
      $new_upload_path = $target_path ? $target_path . '/' . $new_filename : $new_filename;
      
      // 更新文档路径
      $update_data = [
        'filepath' => $target_path,
        'upload_path' => $new_upload_path,
        'updated_at' => date('Y-m-d H:i:s')
      ];
      
      $result = $this->doc_m->updateDocument($doc_id, $update_data);
      
      if ($result) {
        echo json_encode([
          'success' => true,
          'message' => '移动成功',
          'data' => $update_data
        ]);
      } else {
        throw new Exception('移动失败');
      }
    } catch (Exception $e) {
      echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    }
  }

  /**
   * 上传文档API - 支持单一文件、多文件、目录上传
   */
  public function api_upload(): void {
    header('Content-Type: application/json');
    
    try {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('只支持POST请求');
      }
      
      if (!isset($_FILES['files']) || !is_array($_FILES['files']['name'])) {
        throw new Exception('没有文件被上传');
      }
      
      $tenant_id = $this->seg[0] ?? 'default';
      $entity_id = $_POST['entity_id'] ?? '';
      

      
      $files = $_FILES['files'];
      $descriptions = $_POST['descriptions'] ?? [];
      $upload_paths = $_POST['upload_paths'] ?? []; // 用于目录上传的文件路径
      
      $uploaded_files = [];
      $errors = [];
      
      // 处理多个文件
      for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
          $errors[] = "文件 {$files['name'][$i]} 上传失败";
          continue;
        }
        
        try {
          // 生成唯一ID
          $doc_id = randstr();
          
          // 处理文件路径（用于目录上传）
          $file_path = $upload_paths[$i] ?? '';
          $original_name = $files['name'][$i];
          
          // 如果文件路径包含目录，提取目录部分
          $dir_path = '';
          if (!empty($file_path)) {
            $dir_path = dirname($file_path);
            // 确保目录路径不以/开头
            $dir_path = ltrim($dir_path, '/');
          }
          
          // 创建上传目录结构
          $upload_base_dir = APP."../data/{$tenant_id}/upload/";
          $date_dir = date('Ymd');
          $time_prefix = date('His') . '_' . substr(uniqid(), -6);
          
          // 如果有目录路径，添加到上传目录中
          $full_upload_dir = $upload_base_dir . $date_dir . '/';
          if (!empty($dir_path)) {
            $full_upload_dir .= $dir_path . '/';
          }
          
          if (!is_dir($full_upload_dir)) {
            mkdir($full_upload_dir, 0755, true);
          }
          
          // 生成压缩文件名
          $compressed_filename = $time_prefix . '_' . $doc_id . '.gz';
          $compressed_filepath = $full_upload_dir . $compressed_filename;
          
          // 读取上传的文件内容
          $file_content = file_get_contents($files['tmp_name'][$i]);
          
          // 使用gzip压缩文件内容
          $compressed_content = gzencode($file_content, 9);
          
          // 使用SALT常量作为密码进行加密
          $encrypted_content = $this->encryptWithSalt($compressed_content);
          
          // 保存压缩加密后的文件
          if (!file_put_contents($compressed_filepath, $encrypted_content)) {
            throw new Exception("文件保存失败");
          }
          
          // 创建文档记录
          $doc_data = [
            'id' => $doc_id,
            'tenant_id' => $tenant_id,
            'entity_id' => $entity_id,
            'name' => $original_name,
            'filename' => $compressed_filename,
            'filepath' => $compressed_filepath,
            'filetype' => $files['type'][$i],
            'filesize' => filesize($compressed_filepath),
            'original_size' => $files['size'][$i],
            'description' => $descriptions[$i] ?? '',
            'upload_path' => $file_path, // 保存原始上传路径
            'created_by' => $this->user_id ?? 'system'
          ];
          
          $result = $this->doc_m->createDocument($doc_data);
          if (!$result) {
            throw new Exception("文档记录创建失败");
          }
          
          $uploaded_files[] = $doc_data;
          
        } catch (Exception $e) {
          $errors[] = "文件 {$files['name'][$i]} 处理失败: " . $e->getMessage();
        }
      }
      
      if (count($errors) > 0) {
        echo json_encode([
          'success' => false,
          'error' => implode(', ', $errors)
        ]);
      } else {
        echo json_encode([
          'success' => true,
          'message' => '文件上传成功',
          'files' => $uploaded_files
        ]);
      }
      
    } catch (Exception $e) {
      echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    }
  }

  /**
   * 使用SALT常量加密数据
   */
  private function encryptWithSalt($data): string {
    $salt = defined('SALT') ? SALT : 'b2core_salt';
    $key = hash('sha256', $salt, true);
    $iv = substr(hash('sha256', $salt . 'iv'), 0, 16);
    
    return openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
  }
  
  /**
   * 使用SALT常量解密数据
   */
  private function decryptWithSalt($data): string {
    $salt = defined('SALT') ? SALT : 'b2core_salt';
    $key = hash('sha256', $salt, true);
    $iv = substr(hash('sha256', $salt . 'iv'), 0, 16);
    
    return openssl_decrypt($data, 'AES-256-CBC', $key, 0, $iv);
  }

  /**
   * 下载文档API
   * 支持GET和POST请求方式，通过doc_id参数获取文档ID
   * 下载时会自动解密和解压缩文件内容
   */
  public function api_download(): void {
    header('Content-Type: application/json');
    
    try {
      // 同时支持GET和POST请求
      $doc_id = $_REQUEST['doc_id'] ?? '';
      if (empty($doc_id)) {
        throw new Exception('文档ID不能为空');
      }
      
      $doc = $this->doc_m->getDocumentById($doc_id);
      if (!$doc) {
        throw new Exception('文档不存在');
      }
      
      if (!file_exists($doc['filepath'])) {
        throw new Exception('文件不存在');
      }
      
      // 读取加密压缩的文件内容
      $encrypted_content = file_get_contents($doc['filepath']);
      
      // 解密内容
      $compressed_content = $this->decryptWithSalt($encrypted_content);
      
      // 解压缩内容
      $original_content = gzdecode($compressed_content);
      
      // 设置下载头
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename="' . $doc['name'] . '"');
      header('Content-Length: ' . strlen($original_content));
      
      // 输出文件内容
      echo $original_content;
      
    } catch (Exception $e) {
      echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    }
  }

  /**
   * 重命名文档API
   */
  public function api_rename(): void {
    header('Content-Type: application/json');
    
    try {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('只支持POST请求');
      }
      
      $doc_id = $_POST['doc_id'] ?? '';
      $new_name = $_POST['new_name'] ?? '';
      
      if (empty($doc_id) || empty($new_name)) {
        throw new Exception('文档ID和新名称不能为空');
      }
      
      $result = $this->doc_m->updateDocument($doc_id, ['name' => $new_name]);
      
      if ($result) {
        echo json_encode([
          'success' => true,
          'message' => '重命名成功'
        ]);
      } else {
        throw new Exception('重命名失败');
      }
      
    } catch (Exception $e) {
      echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    }
  }

  /**
   * 删除文档API - 软删除
   */
  public function api_delete(): void {
    header('Content-Type: application/json');
    
    try {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('只支持POST请求');
      }
      
      $doc_id = $_POST['doc_id'] ?? '';
      
      if (empty($doc_id)) {
        throw new Exception('文档ID不能为空');
      }
      
      $doc = $this->doc_m->getDocumentById($doc_id);
      if (!$doc) {
        throw new Exception('文档不存在');
      }
      
      // 软删除：设置del=1
      $result = $this->doc_m->softDeleteDocument($doc_id);
      
      if ($result) {
        echo json_encode([
          'success' => true,
          'message' => '删除成功'
        ]);
      } else {
        throw new Exception('删除失败');
      }
      
    } catch (Exception $e) {
      echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    }
  }

  /**
   * 创建MD文件API
   */
  public function api_create_md(): void {
    header('Content-Type: application/json');
    
    try {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('只支持POST请求');
      }
      
      global $seg;
      $tenant_id = $seg[0];
      $entity_id = $_POST['entity_id'] ?? '';
      $filename = $_POST['filename'] ?? '';
      $content = $_POST['content'] ?? '';
      $upload_path = $_POST['upload_path'] ?? '';
      
      if (empty($filename)) {
        throw new Exception('文件名不能为空');
      }
      
      // 确保文件名以.md结尾
      if (!preg_match('/\.md$/i', $filename)) {
        $filename .= '.md';
      }
      
      // 生成唯一ID
      $doc_id = randstr();
      
      // 处理文件路径（用于目录上传）
      $dir_path = '';
      if (!empty($upload_path)) {
        $dir_path = dirname($upload_path);
        // 确保目录路径不以/开头
        $dir_path = ltrim($dir_path, '/');
      }
      
      // 创建上传目录结构，与上传文件使用相同的规则
      $upload_base_dir = APP."../data/{$tenant_id}/upload/";
      $date_dir = date('Ymd');
      $time_prefix = date('His') . '_' . substr(uniqid(), -6);
      
      // 如果有目录路径，添加到上传目录中
      $full_upload_dir = $upload_base_dir . $date_dir . '/';
      if (!empty($dir_path)) {
        $full_upload_dir .= $dir_path . '/';
      }
      
      if (!is_dir($full_upload_dir)) {
        mkdir($full_upload_dir, 0755, true);
      }
      
      // 生成压缩文件名，与上传文件使用相同的命名规则
      $compressed_filename = $time_prefix . '_' . $doc_id . '.gz';
      $compressed_filepath = $full_upload_dir . $compressed_filename;
      
      // 使用gzip压缩文件内容
      $compressed_content = gzencode($content, 9);
      
      // 使用SALT常量作为密码进行加密
      $encrypted_content = $this->encryptWithSalt($compressed_content);
      
      // 保存压缩加密后的文件
      if (!file_put_contents($compressed_filepath, $encrypted_content)) {
        throw new Exception('文件创建失败');
      }
      
      // 创建文档记录
      $doc_data = [
        'id' => $doc_id,
        'tenant_id' => $tenant_id,
        'entity_id' => $entity_id,
        'name' => $filename,
        'filename' => $compressed_filename,
        'filepath' => $compressed_filepath,
        'filetype' => 'text/markdown',
        'filesize' => filesize($compressed_filepath),
        'original_size' => strlen($content),
        'description' => $_POST['description'] ?? '',
        'upload_path' => $upload_path,
        'created_by' => $this->user_id ?? 'system'
      ];
      
      $result = $this->doc_m->createDocument($doc_data);
      if (!$result) {
        throw new Exception('文档记录创建失败');
      }
      
      echo json_encode([
        'success' => true,
        'message' => 'MD文件创建成功',
        'doc_id' => $doc_id
      ]);
      
    } catch (Exception $e) {
      echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    }
  }

  /**
   * 获取MD文件内容API
   */
  public function api_get_md(): void {
    header('Content-Type: application/json');
    
    try {
      $doc_id = $_GET['doc_id'] ?? '';
      
      if (empty($doc_id)) {
        throw new Exception('文档ID不能为空');
      }
      
      $doc = $this->doc_m->getDocumentById($doc_id);
      if (!$doc) {
        throw new Exception('文档不存在');
      }
      
      if (!file_exists($doc['filepath'])) {
        throw new Exception('文件不存在');
      }
      
      // 读取加密压缩的文件内容
      $encrypted_content = file_get_contents($doc['filepath']);
      
      // 解密内容
      $compressed_content = $this->decryptWithSalt($encrypted_content);
      
      // 解压缩内容
      $content = gzdecode($compressed_content);
      
      // 检查解压缩是否成功
      if ($content === false) {
        // 尝试直接读取（兼容旧版本未压缩的文件）
        $content = file_get_contents($doc['filepath']);
        if ($content === false) {
          throw new Exception('读取文件失败');
        }
      }
      
      echo json_encode([
        'success' => true,
        'data' => [
          'doc' => $doc,
          'content' => $content
        ]
      ]);
      
    } catch (Exception $e) {
      echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    }
  }

  /**
   * 更新MD文件内容API
   */
  public function api_update_md(): void {
    header('Content-Type: application/json');
    
    try {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('只支持POST请求');
      }
      
      $doc_id = $_POST['doc_id'] ?? '';
      $content = $_POST['content'] ?? '';
      
      if (empty($doc_id)) {
        throw new Exception('文档ID不能为空');
      }
      
      $doc = $this->doc_m->getDocumentById($doc_id);
      if (!$doc) {
        throw new Exception('文档不存在');
      }
      
      if (!file_exists($doc['filepath'])) {
        throw new Exception('文件不存在');
      }
      
      // 使用gzip压缩文件内容
      $compressed_content = gzencode($content, 9);
      
      // 使用SALT常量作为密码进行加密
      $encrypted_content = $this->encryptWithSalt($compressed_content);
      
      // 更新文件内容
      if (!file_put_contents($doc['filepath'], $encrypted_content)) {
        throw new Exception('文件更新失败');
      }
      
      // 更新文件大小
      $this->doc_m->updateDocument($doc_id, [
        'filesize' => filesize($doc['filepath']),
        'original_size' => strlen($content)
      ]);
      
      echo json_encode([
        'success' => true,
        'message' => 'MD文件更新成功'
      ]);
      
    } catch (Exception $e) {
      echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    }
  }

  /**
   * 删除文档 - 软删除
   */
  public function delete_doc($doc_id = ''): void
  {
    header('Content-Type: application/json');
    
    try {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('只支持POST请求');
      }
      
      global $seg;
      $tenant_id = $seg[0];
      
      // 如果没有传入doc_id，尝试从POST参数中获取
      if (empty($doc_id)) {
        $doc_id = $_POST['doc_id'] ?? '';
        if (empty($doc_id)) {
          throw new Exception('文档ID不能为空');
        }
      }
      
      // 使用doc_m模型查询文档信息
      $doc = $this->doc_m->getDocumentById($doc_id);
      
      if (!$doc || $doc['tenant_id'] !== $tenant_id) {
        throw new Exception('文档不存在');
      }
      
      // 软删除：设置del=1
      $result = $this->doc_m->softDeleteDocument($doc_id);
      
      if (!$result) {
        throw new Exception('数据库删除失败');
      }
      
      echo json_encode(['success' => true, 'message' => '文档删除成功']);
      
    } catch (Exception $e) {
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
  }

}