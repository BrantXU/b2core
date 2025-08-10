<div> 
  <div class="uk-margin uk-flex uk-flex-between uk-flex-middle">
    <div>
      <button id="batchDeleteBtn" class="uk-button uk-button-danger" disabled>批量删除</button>
      <a href="<?=tenant_url('config/edit/') ?>?action=create" class="uk-button uk-button-primary" style="margin-left: 10px;">创建配置</a>
      <a href="<?=tenant_url('config/import') ?>" class="uk-button uk-button-secondary" style="margin-left: 10px;">lpp配置导入</a>
    </div>
  </div>

  <form id="batchDeleteForm" action="<?=tenant_url('config/batch_delete') ?>" method="post">
  
  <?php if(isset($configs) && !empty($configs)): ?>
    <table class="uk-table uk-table-divider uk-table-striped" style="width: 100%;" id="configTable">
      <thead>
        <tr>
          <th><input type="checkbox" id="selectAll"></th>
          <th>键名</th>
          <th>类别</th>
          <th>描述</th>
          <th>更新/创建时间</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($configs as $config): ?>
          <tr>
            <td><input type="checkbox" name="ids[]" value="<?=$config['id']?>" class="configCheckbox"></td>
            <td><?=$config['key']?></td>
            <td><?=$config['config_type']?></td>
            <td><?=$config['description']?></td>
            <td><?=$config['updated_at']?><br /><?=$config['created_at']?></td>
            <td>
              <a href="<?=tenant_url('config/edit')?>?id=<?=$config['id']?>" class="uk-button uk-button-small uk-button-default">编辑</a>
              <a href="<?=tenant_url('config/delete')?>?id=<?=$config['id']?>" class="uk-button uk-button-small uk-button-danger" onclick="return confirm('确定要删除此配置吗？')">删除</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </form>
  
  <div class="uk-margin uk-text-center">
    <span>共 <?=count($configs)?> 条记录</span>
  </div>
  
  <?php else: ?>
    <div class="uk-alert uk-alert-warning">暂无配置数据</div>
  <?php endif; ?>

  <script>
    // 全选/取消全选
    document.addEventListener('DOMContentLoaded', function() {
      const selectAll = document.getElementById('selectAll');
      const checkboxes = document.querySelectorAll('.configCheckbox');
      const batchDeleteBtn = document.getElementById('batchDeleteBtn');
      const batchDeleteForm = document.getElementById('batchDeleteForm');

      // 全选功能
      if (selectAll) {
        selectAll.addEventListener('change', function() {
          checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
          });
          updateBatchButtonState();
        });
      }

      // 单个复选框变化时更新全选状态和批量按钮状态
      checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
          if (selectAll) {
            selectAll.checked = checkboxes.length > 0 && [...checkboxes].every(cb => cb.checked);
          }
          updateBatchButtonState();
        });
      });

      // 更新批量删除按钮状态
      function updateBatchButtonState() {
        const checkedCount = document.querySelectorAll('.configCheckbox:checked').length;
        if (batchDeleteBtn) {
          batchDeleteBtn.disabled = checkedCount === 0;
        }
      }

      // 批量删除按钮点击事件
      if (batchDeleteBtn) {
        batchDeleteBtn.addEventListener('click', function() {
          if (confirm('确定要删除选中的配置吗？')) {
            if (batchDeleteForm) {
              batchDeleteForm.submit();
            }
          }
        });
      }
    });



    const enhancer = new TableEnhancer('configTable', {
      pageSize: 10,
      searchable: true,
      sortable: true
    });
    
  </script>
</div>