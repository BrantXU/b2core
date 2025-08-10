# 表格增强器使用指南

## 简介
表格增强器是一个轻量级的JavaScript工具，可以将任何传统的HTML表格转换为支持自动分页、实时搜索和列排序的增强表格。

## 使用方法

### 方法1：使用完整版（TableEnhancer类）

```javascript
// 引入文件
<script src="tableEnhancer.js"></script>

// 基本使用
const enhancer = new TableEnhancer('tableId');

// 自定义配置
const enhancer = new TableEnhancer('tableId', {
    pageSize: 20,
    searchable: true,
    sortable: true,
    showInfo: true,
    showPagination: true,
    searchPlaceholder: '搜索数据...',
    pageSizes: [10, 20, 50, 100]
});
```

### 方法2：使用简化版（enhanceTable函数）

```javascript
// 引入文件
<script src="tableEnhancer-simple.js"></script>

// 一行代码转换
enhanceTable('tableId');

// 或带配置
const table = enhanceTable('tableId', {
    pageSize: 15,
    searchable: true,
    sortable: true
});
```

## HTML结构要求

表格需要标准的HTML结构：

```html
<table id="yourTableId" class="uk-table">
    <thead>
        <tr>
            <th>列1</th>
            <th>列2</th>
            <th>列3</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>数据1</td>
            <td>数据2</td>
            <td>数据3</td>
        </tr>
        <!-- 更多行... -->
    </tbody>
</table>
```

## 配置选项

| 选项 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| pageSize | number | 10 | 每页显示条数 |
| pageSizes | array | [10,20,50,100] | 可选的每页显示条数 |
| searchable | boolean | true | 是否启用搜索功能 |
| sortable | boolean | true | 是否启用排序功能 |
| showInfo | boolean | true | 是否显示记录信息 |
| showPagination | boolean | true | 是否显示分页控件 |
| searchPlaceholder | string | '搜索...' | 搜索框占位符文本 |

## API方法

### TableEnhancer类方法

```javascript
const enhancer = new TableEnhancer('tableId');

// 刷新数据（重新从原始表格提取）
enhancer.refresh();

// 跳转到指定页
enhancer.goToPage(3);

// 获取当前数据
const data = enhancer.getData();
```

### enhanceTable函数返回值

```javascript
const table = enhanceTable('tableId');

// 刷新数据
table.refresh();

// 跳转到指定页
table.goToPage(2);

// 获取当前数据
const data = table.getData();
```

## 样式要求

表格增强器使用UIkit框架样式，需要引入：

```html
<!-- UIkit CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.16.26/dist/css/uikit.min.css" />

<!-- UIkit JS -->
<script src="https://cdn.jsdelivr.net/npm/uikit@3.16.26/dist/js/uikit.min.js"></script>
```

## 使用示例

### 示例1：基本使用

```html
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.16.26/dist/css/uikit.min.css" />
</head>
<body>
    <table id="userTable" class="uk-table">
        <thead>
            <tr>
                <th>姓名</th>
                <th>年龄</th>
                <th>城市</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>张三</td><td>25</td><td>北京</td></tr>
            <tr><td>李四</td><td>30</td><td>上海</td></tr>
            <!-- 更多数据... -->
        </tbody>
    </table>

    <script src="https://cdn.jsdelivr.net/npm/uikit@3.16.26/dist/js/uikit.min.js"></script>
    <script src="tableEnhancer.js"></script>
    <script>
        // 转换表格
        new TableEnhancer('userTable');
    </script>
</body>
</html>
```

### 示例2：PHP动态数据

```php
<table id="configTable" class="uk-table">
    <thead>
        <tr>
            <th>配置键</th>
            <th>值</th>
            <th>描述</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($configs as $config): ?>
        <tr>
            <td><?= htmlspecialchars($config['key']) ?></td>
            <td><?= htmlspecialchars($config['value']) ?></td>
            <td><?= htmlspecialchars($config['description']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script src="tableEnhancer.js"></script>
<script>
    new TableEnhancer('configTable', {
        pageSize: 15,
        searchable: true,
        sortable: true
    });
</script>
```

## 兼容性

- 现代浏览器（Chrome, Firefox, Safari, Edge）
- IE11+（需要Promise polyfill）
- 移动端浏览器

## 注意事项

1. 表格必须有`id`属性
2. 必须有完整的`thead`和`tbody`结构
3. 数据会缓存在内存中，大数据量时请注意性能
4. 排序功能会自动识别数字和字符串类型
5. 搜索功能会搜索所有列的内容

## 文件说明

- `tableEnhancer.js` - 完整版，功能全面，面向对象设计
- `tableEnhancer-simple.js` - 简化版，使用简单，函数式设计
- `tableEnhancer-demo.html` - 演示文件，展示所有功能
- `tableEnhancer-usage.md` - 本使用指南