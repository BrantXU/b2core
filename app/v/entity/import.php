<div class="uk-container uk-padding">
    <h3 class="uk-heading-medium">导入实体数据</h3>

    <?php if (!empty($err['general'])): ?>
        <div class="uk-alert uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p><?= htmlspecialchars($err['general']) ?></p>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="uk-form-stacked">
        <div class="uk-margin">
            <label class="uk-form-label" for="import_file">选择CSV文件</label>
            <div class="uk-form-controls">
                <input class="uk-input" type="file" id="import_file" name="import_file" accept=".csv" required>
                <div class="uk-form-help-text">
                    请上传CSV格式文件，第一行需包含表头（name字段为必填项）
                </div>
            </div>
        </div>

        <div class="uk-margin">
            <button type="submit" class="uk-button uk-button-primary">导入数据</button>
            <a href="<?= tenant_url('entity/') ?>" class="uk-button uk-button-default">取消</a>
        </div>
    </form>
</div>