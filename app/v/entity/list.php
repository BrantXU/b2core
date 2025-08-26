<?=$table_content?>

<script>
document.querySelectorAll("table").forEach(table => { 
      const enhancer = new TableEnhancer(table.id, {
      pageSize: 10,
      searchable: true,
      sortable: true
    });
});
</script>