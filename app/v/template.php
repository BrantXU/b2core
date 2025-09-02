<!DOCTYPE html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title><?php echo $page_title ?? ''; ?></title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="keywords" content="<?php echo $meta_keywords ?? ''; ?>">
  <meta name="description" content="<?php echo $meta_description ?? ''; ?>">
  <link href="/static/css/uikit.min.css" rel="stylesheet" type="text/css">
  <script src="/static/js/uikit.min.js"></script>
  <script src="/static/js/uikit-icons.min.js"></script>
  <script src="/static/js/base.js"></script>
  <script src="/static/js/tableEnhancer.js"></script>
  <script src="/static/js/modal.js"></script>
  <link href="https://unpkg.com/ionicons@4.5.10-0/dist/css/ionicons.min.css" rel="stylesheet">
  <style>
    body{background: #f6f6f6;line-height: 1.5;}
    del{color:#F33;}
    h3.uk-first-column{padding:0 15px;}
    .uk-form{max-width: 800px;}
    *+.uk-grid-margin, .uk-grid+.uk-grid, .uk-grid>.uk-grid-margin{margin-top: 10px;}
    .canvas{background: #fff;padding: 20px;}
    .container{padding: 0 10px;}
    a{color: #000;}
    .help-inline{color: #f00;}
    .top-menu li>a, .sub-menu li>a{min-height: 50px; padding: 0.3rem 0.5rem;}
    /* 调整导航菜单样式 */
    .uk-navbar-nav > li > a {
      padding: 0 15px;
      line-height: 40px;
      white-space: nowrap;
    }
    .uk-dropdown{padding: 3px;font-size:.875rem;}
    .uk-navbar-nav > li.uk-parent > a::after {
      content: "";
      margin-left: 6px;
      vertical-align: middle;
    }
    
    .uk-nav-sub {
      padding-left: 15px;
    }
    
    .uk-nav-sub .uk-nav-sub {
      padding-left: 15px;
    }
    /* 两排菜单样式 */
    .top-menu {
      background: #f8f8f8;
      border-bottom: 1px solid #e5e5e5;
    }
    
    .sub-menu {
      background: #fff;
      padding: 10px 0;
    }
    
    .sub-menu .uk-navbar-nav > li > a {
      line-height: 30px;
    }
    .top-menu li>a, .uk-dropdown-navbar .uk-nav-navbar li>a{min-height: 50px; padding: 0.3rem 1rem; background: #fff; color: #000; line-height: 50px;}
    .top-menu li>a:hover, .uk-dropdown-navbar .uk-nav-navbar li>a:hover{background: #f5f5f5;}
    .top-menu li.uk-active>a, .uk-dropdown-navbar .uk-nav-navbar li.uk-active>a{background: #e0e0e0; font-weight: bold;}
    .uk-navbar-nav, .uk-nav-navbar{gap:0px;}
    .usr-panel{float:right;}

    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .modal-content {
      position: relative;
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      z-index: 1001;
    }

</style>
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
</body>
</html>
