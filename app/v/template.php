<!DOCTYPE html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title><?php echo $page_title ?? ''; ?></title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="keywords" content="<?php echo $meta_keywords ?? ''; ?>">
  <meta name="description" content="<?php echo $meta_description ?? ''; ?>">
  <link href="/static/css/uikit.min.css" rel="stylesheet" type="text/css">
  <link href="/static/css/base.css" rel="stylesheet" type="text/css">
  <script src="/static/js/uikit.min.js"></script>
  <script src="/static/js/base.js"></script>
  <script src="/static/js/tableRender.js"></script>
  <script src="/static/js/modal.js"></script>
  <link href="https://unpkg.com/ionicons@4.5.10-0/dist/css/ionicons.min.css" rel="stylesheet">
</head>
<body>
  <div> 
  <?php if(!empty($menu_data) && is_array($menu_data) && empty($hide_menu)): ?>
  <!-- 顶部主菜单 -->
  <nav class="uk-navbar-container top-menu" uk-navbar>
    <div class="uk-navbar-left">
      <?=render_top_menu($menu_data)?>
    </div>
  </nav>
  
  <!-- 附菜单 -->
  <nav class="uk-navbar-container sub-menu" uk-navbar>
    <div class="uk-navbar-left">
      <?=render_sub_menu($menu_data)?>
    </div>
  </nav>
  <?php endif; ?>
</div>
<div class="container"> 
  <div class="canvas">
    <?php if (!empty($breadcrumb)): ?>
    <nav >
      <ul class="uk-breadcrumb">
        <?php foreach ($breadcrumb as $item): ?>
          <li><?php echo ($item['active'] ?? false) ? '<span>' . ($item['label'] ?? 'Unlabeled') . '</span>' : '<a href="' . $item['url'] . '" class="uk-link-reset">' . ($item['label'] ?? 'Unlabeled') . '</a>'; ?></li>
        <?php endforeach; ?>
      </ul>
    </nav>


    <?php endif; ?>

    <!-- 渲染对象菜单 -->
    <?php if (!empty($object_menu_key) && !empty($object_id) && !empty($menu_data)): ?>
      <?=render_object_menu($menu_data, $object_id, $object_menu_key)?>
    <?php endif; ?>

    <?php if(isset($al_content)) echo $al_content; else echo $content ?? ''; ?>
    <?php if(!empty($log)): ?>
    <pre>
    <?php print_r($log); ?>
    </pre>
    <?php endif; ?>
  </div>

  <script>
  // 在页面加载完成后统一渲染所有表格
  document.addEventListener('DOMContentLoaded', function() {
    // 检查是否存在表格配置
    if (typeof window.tableConfigs !== 'undefined' && Array.isArray(window.tableConfigs)) {
      // 遍历所有表格配置并初始化
      window.tableConfigs.forEach(function(config) {
        try {
          // 检查必要的配置项
          if (config.containerId && config.baseUrl) {
            // 创建TableRender实例
            const enhancer = new TableRender(
              config.containerId,
              {
                pageSize: config.pageSize || 10,
                searchable: config.searchable || true,
                data: config.data || [],
                fields: config.fields || []
              },
              config.baseUrl
            );
          }
        } catch (error) {
          console.error('Error initializing table:', config.containerId, error);
        }
      });
      
      // 清理配置数组，防止重复初始化
      window.tableConfigs = [];
    }
  });
  </script>
</body>
</html>
