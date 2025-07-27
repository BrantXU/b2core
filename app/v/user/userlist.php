<div>
  <h2>用户管理</h2>
<a href="/user/create" class="pure-button pure-button-primary">创建用户</a>
<table class="pure-table pure-table-horizontal" style="width: 100%;">
    <thead>
        <tr>
            <th>用户名</th>
            <th width="150">操作</th>
        </tr>
    </thead>
    <tbody>
        <?php if(empty($users)): ?>
            <tr>
                <td colspan="2">暂无用户数据</td>
            </tr>
        <?php else: ?>
            <?php foreach($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td>
                    <a href="/user/edit?id=<?php echo $user['id']; ?>" class="pure-button pure-button-primary">编辑</a>
                    <a href="/user/delete?id=<?php echo $user['id']; ?>" class="pure-button" onclick="return confirm('确定要删除这个用户吗？')">删除</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
</div>
