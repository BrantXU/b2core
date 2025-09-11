<div> 
  <div id="configTable"></div>
  <script>
    // 准备表格数据
    const tableData = [
      <?php foreach ($configs as $config): ?>
        {
          id: '<?=$config['id']?>',
          key: '<?=addslashes($config['key'])?>',
          mod: '<?=renderConfigType($config['config_type'])?>',
          desc: '<?=addslashes($config['description'])?>',
          time: '<?=addslashes($config['updated_at'])?>\n<?=addslashes($config['created_at'])?>'
        },
      <?php endforeach; ?>
    ];

    const enhancer = new TableRender('configTable', {
      pageSize: 10,
      searchable: true,
      sortable: true,
      enableCheckbox: true,
      showExport: true,
      showImport: true,
      showCreate: true,
      data: tableData,
      fields: {
        key: { label: '键名', name: 'key' },
        mod: { label: '类别', name: 'mod' },
        desc: { label: '描述', name: 'desc' },
        time: { label: '时间', name: 'time' }
      }
    }, '<?=tenant_url('config/') ?>');
    
  </script>
</div>