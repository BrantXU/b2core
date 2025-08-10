<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="refresh" content="1;url=<?=$url?>" />
  <link href="/static/css/uikit.min.css" rel="stylesheet" type="text/css">
  <title> <?=$msg?> </title>
</head>
<body>
<div class="uk-container uk-position-center">
  <h1 class="uk-heading-medium"><?=$msg?></h1>
  <div><?=$ext_msg?></div>
  <div class="uk-margin">页面跳转至: <a href="<?=$url?>" class="uk-link"><?=$url?></a>
   <br />你可以点击 <a href="<?=$url?>" class="uk-link">直接前往</a>
  </div>
</div>
</body>
</html>
