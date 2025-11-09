<?php
class doc_m extends m {
  public $table;
  public $fields;
  
  public function __construct() 
  {
    global $tdb;
    if (isset($tdb)) {
      $this->db = $tdb;
    }
    $this->table = 'tb_document';
    $this->key = 'id';
    $this->conditions = [];
    $this->fields = array('id', 'del','tenant_id', 'entity_id', 'name', 'filename', 'filepath', 'filetype', 'filesize', 'original_size', 'upload_path', 'description', 'created_by', 'created_at', 'updated_at');
  }

  /**
   * 获取实体相关的文档列表（过滤已删除记录）
   * @param string $entityId 实体ID
   * @param int $page 页码
   * @param int $limit 每页记录数
   * @return array
   */
  public function getDocumentsByEntity($entityId, int $page = 1, $limit = 20) {
    $entityId = $this->db->escape($entityId);
    $start = ($page - 1) * $limit;
    $query = "SELECT * FROM tb_document 
              WHERE entity_id = '$entityId' AND del = 0
              ORDER BY created_at DESC
              LIMIT $start, $limit";
    return $this->db->query($query);
  }

  /**
   * 根据上传路径获取文档（过滤已删除记录）
   * @param string $entityId 实体ID
   * @param string $uploadPath 上传路径
   * @return array|null
   */
  public function getDocumentByUploadPath($entityId, $uploadPath) {
    $entityId = $this->db->escape($entityId);
    $uploadPath = $this->db->escape($uploadPath);
    $query = "SELECT * FROM tb_document 
              WHERE entity_id = '$entityId' AND upload_path = '$uploadPath' AND del = 0
              LIMIT 1";
    $result = $this->db->query($query);
    return $result[0] ?? null;
  }

  /**
   * 创建文档记录
   * @param array $data 文档数据
   * @return int|bool
   */
  public function createDocument($data) {
    // 如果未提供ID，则生成ID
    if (!isset($data['id']) || empty($data['id'])) {
      $data['id'] = randstr(32);
    }
    
    // 设置创建时间
    if (!isset($data['created_at'])) {
      $data['created_at'] = date('Y-m-d H:i:s');
    }
    
    // 设置更新时间
    if (!isset($data['updated_at'])) {
      $data['updated_at'] = date('Y-m-d H:i:s');
    }
    
    return $this->add($data);
  }

  /**
   * 软删除文档记录
   * @param string $id 文档ID
   * @return bool
   */
  public function softDeleteDocument($id) {
    return $this->update($id, ['del' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
  }

  /**
   * 物理删除文档记录（谨慎使用）
   * @param string $id 文档ID
   * @return bool
   */
  public function deleteDocument($id) {
    return $this->del($id);
  }

  /**
   * 更新文档记录
   * @param string $id 文档ID
   * @param array $data 更新数据
   * @return bool
   */
  public function updateDocument($id, $data) {
    return $this->update($id, $data);
  }

  /**
   * 获取文档详情（过滤已删除记录）
   * @param string $id 文档ID
   * @return array|null
   */
  public function getDocumentById($id) {
    $id = $this->db->escape($id);
    $query = "SELECT * FROM tb_document WHERE id = '$id' AND del = 0 LIMIT 1";
    $result = $this->db->query($query);
    return $result[0] ?? null;
  }

  /**
   * 获取文档详情（包含已删除记录）
   * @param string $id 文档ID
   * @return array|null
   */
  public function getDocumentByIdWithDeleted($id) {
    return $this->getOne($id);
  }

  /**
   * 获取实体文档总数（过滤已删除记录）
   * @param string $tenantId 租户ID
   * @param string $entityId 实体ID
   * @return int
   */
  public function getDocumentCountByEntity($tenantId, $entityId) {
    $tenantId = $this->db->escape($tenantId);
    $entityId = $this->db->escape($entityId);
    $query = "SELECT COUNT(*) as count FROM tb_document 
              WHERE tenant_id = '$tenantId' AND entity_id = '$entityId' AND del = 0";
    $result = $this->db->query($query);
    return $result[0]['count'] ?? 0;
  }

  /**
   * 根据文件名查找文档（过滤已删除记录）
   * @param string $filename 文件名
   * @return array|null
   */
  public function getDocumentByFilename($filename) {
    $filename = $this->db->escape($filename);
    $query = "SELECT * FROM tb_document WHERE filename = '$filename' AND del = 0 LIMIT 1";
    $result = $this->db->query($query);
    return $result[0] ?? null;
  }
}