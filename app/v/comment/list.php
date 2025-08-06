<?php if (!defined('B2CORE')) exit('No direct script access allowed'); ?>
<div class="comment-container">
  <h3>评论列表</h3>
  
  <!-- 评论表单 -->
  <div class="comment-form">
    <form id="commentForm" class="pure-form pure-form-stacked">
      <input type="hidden" name="page_id" value="<?=$pageId?>">
      <div class="pure-control-group">
        <label for="content">评论内容</label>
        <textarea id="content" name="content" rows="5" required></textarea>
      </div>
      <div class="pure-controls">
        <button type="submit" class="pure-button pure-button-primary">提交评论</button>
      </div>
    </form>
  </div>
  
  <!-- 评论列表 -->
  <div class="comment-list">
    <?php if(isset($comments) && !empty($comments)): ?>
      <?php foreach ($comments as $comment): ?>
        <div class="comment-item">
          <div class="comment-header">
            <span class="comment-username"><?=$comment['username']?></span>
            <span class="comment-time"><?=$comment['created_at']?></span>
          </div>
          <div class="comment-content">
            <?=$comment['content']?>
          </div>
        </div>
      <?php endforeach; ?>
    
      <!-- 分页 -->
      <?php if($page_count > 1): ?>
        <div class="pagination">
          <?php for($i=1; $i<=$page_count; $i++): ?>
            <a href="javascript:void(0);" class="page-link <?=($i == $current_page) ? 'active' : ''?>" data-page="<?=$i?>"><?=$i?></a>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="pure-alert">暂无评论数据</div>
    <?php endif; ?>
  </div>
</div>

<script>
// 提交评论
document.getElementById('commentForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  
  fetch('<?=tenant_url('comment/create/')?>', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if(data.code === 0) {
      alert('评论成功');
      // 清空表单
      this.reset();
      // 重新加载评论
      loadComments(1);
    } else {
      alert(data.msg);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('提交失败，请重试');
  });
});

// 加载评论
function loadComments(page) {
  fetch('<?=tenant_url('comment/getComments/')?>?page_id=<?=$pageId?>&page=' + page)
  .then(response => response.json())
  .then(data => {
    if(data.code === 0) {
      const commentList = document.querySelector('.comment-list');
      let html = '';
      
      if(data.data.comments.length > 0) {
        data.data.comments.forEach(comment => {
          html += `
            <div class="comment-item">
              <div class="comment-header">
                <span class="comment-username">${comment.username}</span>
                <span class="comment-time">${comment.created_at}</span>
              </div>
              <div class="comment-content">
                ${comment.content}
              </div>
            </div>
          `;
        });
        
        // 分页
        if(data.data.page_count > 1) {
          html += `
            <div class="pagination">
          `;
          for(let i=1; i<=data.data.page_count; i++) {
            html += `
              <a href="javascript:void(0);" class="page-link ${(i == data.data.current_page) ? 'active' : ''}" data-page="${i}">${i}</a>
            `;
          }
          html += `
            </div>
          `;
        }
      } else {
        html = '<div class="pure-alert">暂无评论数据</div>';
      }
      
      commentList.innerHTML = html;
      
      // 绑定分页事件
      document.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', function() {
          loadComments(this.dataset.page);
        });
      });
    } else {
      alert(data.msg);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('加载失败，请重试');
  });
}

// 初始化时加载评论
loadComments(<?=$current_page?>);
</script>

<style>
.comment-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.comment-form {
  margin-bottom: 30px;
  padding: 15px;
  background-color: #f9f9f9;
  border-radius: 5px;
}

.comment-item {
  margin-bottom: 15px;
  padding: 15px;
  border-bottom: 1px solid #eee;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.comment-username {
  font-weight: bold;
}

.comment-time {
  color: #999;
  font-size: 12px;
}

.pagination {
  margin-top: 20px;
  text-align: center;
}

.page-link {
  display: inline-block;
  padding: 5px 10px;
  margin: 0 5px;
  border: 1px solid #ddd;
  border-radius: 3px;
  text-decoration: none;
  color: #333;
}

.page-link.active {
  background-color: #0078e7;
  color: white;
  border-color: #0078e7;
}
</style>