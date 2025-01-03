
<table>
<?php 
if(isset($user)){
	foreach ($user as $u)
	{
?>
<div class="row">
  <div class="span3"><?=$u['username']?></div>
  <div class="span2"><a href="<?=BASE ?>/index.php/user/userdelete?id=<?=$u['id']?>">删除</a></div>
  <div class="span2"><a href="<?=BASE ?>/index.php/user/update?id=<?=$u['id']?>">修改</a></div>
</div>

<tr><td></td>
	<td></td>
	<td></td>
	<td></td>
</tr>		
<?php 
	}
}
?>
</table>
