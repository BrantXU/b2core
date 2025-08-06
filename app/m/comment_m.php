<?php
class comment_m extends m {
  public $table;
  public $fields;
  
  public function __construct() {
    parent::__construct('tb_comment');
    $this->fields = array('id', 'content', 'user_id', 'page_id', 'created_at', 'updated_at', 'status');
  }

  /**
   * 获取页面评论列表
   * @param string $pageId 页面ID
   * @param int $page 页码
   * @param int $limit 每页记录数
   * @return array
   */
  public function getCommentsByPageId($pageId, $page = 1, $limit = 20) {
    $pageId = $this->db->escape($pageId);
    $start = ($page - 1) * $limit;
    $query = "SELECT c.*, u.username FROM tb_comment c
              LEFT JOIN tb_user u ON c.user_id = u.id
              WHERE c.page_id = '$pageId' AND c.status = 1
              ORDER BY c.created_at DESC
              LIMIT $start, $limit";
    return $this->db->query($query);
  }

  /**
   * 创建评论
   * @param array $data 评论数据
   * @return int|bool
   */
  public function createComment($data) {
    // 生成ID
    $data['id'] = randstr(32);
    return $this->add($data);
  }

  /**
   * 删除评论
   * @param string $id 评论ID
   * @return bool
   */
  public function deleteComment($id) {
    return $this->del($id);
  }

  /**
   * 获取评论总数
   * @param string $pageId 页面ID
   * @return int
   */
  public function getCommentCount($pageId) {
    $pageId = $this->db->escape($pageId);
    $query = "SELECT COUNT(*) as count FROM tb_comment WHERE page_id = '$pageId' AND status = 1";
    $result = $this->db->query($query);
    return $result[0]['count'] ?? 0;
  }
}
?>