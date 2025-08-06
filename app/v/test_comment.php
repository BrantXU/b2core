<?php if (!defined('B2CORE')) exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>评论模块测试</title>
  <link rel="stylesheet" href="https://unpkg.com/purecss@2.0.6/build/pure-min.css" integrity="sha384-Uu6IeWbM+gzNVXJcM9XV3SohHtmWE+3VGi496jvgX1jyvDTXfdK+rfZc8C1Aehk5" crossorigin="anonymous">
</head>
<body>
  <div class="pure-g">
    <div class="pure-u-1-1">
      <h1>评论模块测试页面</h1>
      <p>这是一个测试页面，演示如何通过Ajax加载评论模块。</p>
      
      <!-- 页面ID，用于标识当前页面的评论 -->
      <input type="hidden" id="pageId" value="test_page_1">
      
      <!-- 评论模块容器 -->
      <div id="commentModule"></div>
    </div>
  </div>

  <script>
  // 页面加载完成后加载评论模块
  document.addEventListener('DOMContentLoaded', function() {
    const pageId = document.getElementById('pageId').value;
    loadCommentModule(pageId);
  });

  // 加载评论模块
  function loadCommentModule(pageId) {
    const commentModule = document.getElementById('commentModule');
    
    // 显示加载中...
    commentModule.innerHTML = '<div class="loading">加载评论中...</div>';
    
    // 通过Ajax加载评论模块
    fetch('<?=tenant_url('comment/')?>?page_id=' + pageId)
    .then(response => response.text())
    .then(html => {
      commentModule.innerHTML = html;
    })
    .catch(error => {
      console.error('Error:', error);
      commentModule.innerHTML = '<div class="pure-alert pure-alert-error">加载评论失败</div>';
    });
  }
  </script>

  <style>
  body {
    padding: 20px;
  }
  .loading {
    text-align: center;
    padding: 20px;
    color: #666;
  }
  </style>
</body>
</html>