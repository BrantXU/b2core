<?=$table_content?>

<script>
document.querySelectorAll("table").forEach(table => { 
      const enhancer = new TableEnhancer(table.id, {
      pageSize: 10,
      searchable: true,
      sortable: true,
      enableCheckbox: true,
      showExport: true,
      showImport: true,
      showCreate: true,
      exportUrl: '<?= tenant_url($entity_type.'/export/') ?>',
      importUrl: '<?= tenant_url($entity_type.'/import/') ?>',
      createUrl: '<?= tenant_url($entity_type.'/add/') ?>'
    });
});
</script>