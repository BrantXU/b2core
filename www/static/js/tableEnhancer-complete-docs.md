# TableEnhancer - 表格增强器

TableEnhancer是一个轻量级的JavaScript表格增强库，能够将普通的HTML表格转换为支持分页、搜索、排序、复选框选择和批量操作的增强表格。

## 功能特点

- **分页功能**：支持自定义每页显示条数，提供页码导航
- **搜索功能**：实时搜索表格内容
- **排序功能**：支持点击表头进行列排序
- **复选框选择**：支持单行选择、当前页全选和全部全选
- **批量操作**：支持编辑（单行）、删除（多行）操作
- **数据导入/导出**：支持数据的导入和导出功能
- **响应式设计**：基于UIkit框架，提供良好的响应式体验

## 目录结构

```
project/
└── www/
    └── static/
        └── js/
            ├── tableEnhancer.js     # 核心实现文件
            └── tableEnhancer-complete-docs.md  # 完整文档
```

## 依赖

- **UIkit**：用于样式和组件
  - `www/static/js/uikit.min.js`
  - `www/static/css/uikit.min.css`
- **Ionicons**：用于图标
  - `www/static/css/ionicons.min.css`

## 使用方法

### 1. 引入必要的CSS和JS文件

```html
<!-- UIkit CSS -->
<link rel="stylesheet" href="path/to/uikit.min.css">
<!-- Ionicons CSS -->
<link rel="stylesheet" href="path/to/ionicons.min.css">

<!-- UIkit JS -->
<script src="path/to/uikit.min.js"></script>
<!-- TableEnhancer JS -->
<script src="path/to/tableEnhancer.js"></script>
```

### 2. 创建基础HTML表格

```html
<table id="myTable" class="uk-table uk-table-striped uk-table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>名称</th>
            <th>状态</th>
            <th>创建时间</th>
        </tr>
    </thead>
    <tbody>
        <!-- 表格数据行 -->
        <tr data-row-id="1">
            <td>1</td>
            <td>项目A</td>
            <td>活跃</td>
            <td>2023-01-01</td>
        </tr>
        <!-- 更多行... -->
    </tbody>
</table>
```

### 3. 初始化TableEnhancer

```javascript
// 基本初始化
const enhancer = new TableEnhancer('myTable');

// 带自定义配置的初始化
const enhancer = new TableEnhancer('myTable', {
    pageSize: 20,              // 每页显示条数
    searchable: true,          // 启用搜索功能
    sortable: true,            // 启用排序功能
    enableCheckbox: true,      // 启用复选框
    showExport: true,          // 显示导出按钮
    showImport: true,          // 显示导入按钮
    showCreate: true,          // 显示创建按钮
    showEdit: true,            // 显示编辑按钮
    showDelete: true,          // 显示删除按钮
    exportUrl: '/export',      // 导出URL
    importUrl: '/import',      // 导入URL
    createUrl: '/create',      // 创建URL
    editUrl: '/edit/',         // 编辑URL（会追加ID）
    deleteUrl: '/delete'       // 删除URL（会添加查询参数）
});
```

## 配置选项

| 选项 | 类型 | 默认值 | 描述 |
|------|------|-------|------|
| `pageSize` | Number | 10 | 每页显示的记录数量 |
| `pageSizes` | Array | [10, 20, 50, 100] | 可选的每页显示条数列表 |
| `showInfo` | Boolean | true | 是否显示分页信息 |
| `showPagination` | Boolean | true | 是否显示分页控件 |
| `searchable` | Boolean | true | 是否启用搜索功能 |
| `sortable` | Boolean | true | 是否启用排序功能 |
| `searchPlaceholder` | String | '搜索...' | 搜索框的占位符文本 |
| `enableCheckbox` | Boolean | true | 是否启用复选框选择功能 |
| `showExport` | Boolean | true | 是否显示导出按钮 |
| `showImport` | Boolean | true | 是否显示导入按钮 |
| `showCreate` | Boolean | true | 是否显示创建按钮 |
| `showEdit` | Boolean | true | 是否显示编辑按钮 |
| `showDelete` | Boolean | true | 是否显示删除按钮 |
| `exportUrl` | String | '' | 导出功能的URL |
| `importUrl` | String | '' | 导入功能的URL |
| `createUrl` | String | '' | 创建功能的URL |
| `editUrl` | String | '' | 编辑功能的URL（会追加ID） |
| `deleteUrl` | String | '' | 删除功能的URL（会添加查询参数） |

## API参考

### 构造函数

```javascript
new TableEnhancer(tableId, options)
```
- **参数**:
  - `tableId`: 字符串，表格元素的ID
  - `options`: 对象，可选的配置项
- **返回值**: TableEnhancer实例

### 实例方法

#### extractData()
从表格中提取数据到内部数据结构。
- **参数**: 无
- **返回值**: 无

#### createControls()
创建表格上方的控制面板，包括搜索框、按钮组等。
- **参数**: 无
- **返回值**: 无

#### bindEvents()
为表格及控件绑定事件处理函数。
- **参数**: 无
- **返回值**: 无

#### render()
渲染表格数据，包括分页数据、复选框等。
- **参数**: 无
- **返回值**: 无

#### search(query)
根据查询条件搜索表格数据。
- **参数**:
  - `query`: 字符串，搜索查询条件
- **返回值**: 无

#### sort(columnIndex)
对指定列进行排序。
- **参数**:
  - `columnIndex`: 数字，列索引
- **返回值**: 无

#### updateInfo()
更新分页信息显示。
- **参数**: 无
- **返回值**: 无

#### renderPagination()
渲染分页控件。
- **参数**: 无
- **返回值**: 无

#### toggleSelectAll()
切换全选/取消全选所有数据。
- **参数**: 无
- **返回值**: 无

#### toggleSelectCurrentPage()
切换全选/取消全选当前页数据。
- **参数**: 无
- **返回值**: 无

#### toggleRowSelection(rowId)
切换单行的选择状态。
- **参数**:
  - `rowId`: 字符串，行ID
- **返回值**: 无

#### clearSelection()
清除所有选择。
- **参数**: 无
- **返回值**: 无

#### getSelectedRows()
获取所有选中的行数据。
- **参数**: 无
- **返回值**: 数组，选中的行数据

#### handleEdit()
处理编辑操作（跳转到编辑页面）。
- **参数**: 无
- **返回值**: 无

#### handleDelete()
处理删除操作（显示确认对话框并执行删除）。
- **参数**: 无
- **返回值**: 无

#### updateEditDeleteButtons()
根据选中行的数量更新编辑和删除按钮的显示状态。
- **参数**: 无
- **返回值**: 无

#### updateSortIcons()
更新排序图标的状态。
- **参数**: 无
- **返回值**: 无

#### refresh()
刷新表格数据。
- **参数**: 无
- **返回值**: 无

#### getData()
获取当前过滤后的数据。
- **参数**: 无
- **返回值**: 数组，当前过滤后的数据

#### goToPage(page)
跳转到指定页码。
- **参数**:
  - `page`: 数字，目标页码
- **返回值**: 无

## 内部私有方法

以下方法为内部实现，一般不需要直接调用：

- `_getDefaultOptions(customOptions)`: 获取合并后的默认配置
- `_getControlPanelHtml()`: 获取控制面板HTML
- `_getSearchHtml()`: 获取搜索框HTML
- `_getCheckboxButtonsHtml()`: 获取复选框按钮组HTML
- `_getImportExportButtonsHtml()`: 获取导入导出创建按钮组HTML
- `_getEditDeleteButtonsHtml()`: 获取编辑删除按钮组HTML
- `_getPageSizeSelectorHtml()`: 获取页面大小选择器HTML
- `_createInfoPanel(container)`: 创建信息面板
- `_createPagination(container)`: 创建分页控件
- `_bindSearchEvent()`: 绑定搜索事件
- `_bindPageSizeEvent()`: 绑定分页大小事件
- `_bindSortEvent()`: 绑定排序事件
- `_bindCheckboxEvents()`: 绑定复选框相关事件
- `_bindEditDeleteEvents()`: 绑定编辑删除按钮事件
- `_renderEmptyData(tbody)`: 渲染空数据状态
- `_updateTableHeader()`: 更新表格表头
- `_renderTableRows(tbody)`: 渲染表格数据行
- `_getPaginationHtml(totalPages)`: 获取分页控件HTML
- `_bindPaginationEvents(pagination, totalPages)`: 绑定分页控件事件

## 工作原理

1. **数据提取**：初始化时从表格中提取数据并存储在内部数组中
2. **控件创建**：创建搜索框、按钮组、分页等UI控件
3. **事件绑定**：为各个控件绑定事件处理函数
4. **数据渲染**：根据当前页码、搜索条件、排序状态渲染表格数据
5. **状态维护**：维护选择状态、排序状态、分页状态等

## 常见问题及解决方案

### 1. 表格数据不显示或显示不正确
- 确保表格的ID与初始化时传入的ID一致
- 确保表格结构正确，包含thead和tbody
- 检查控制台是否有错误信息

### 2. 分页功能不工作
- 确保`showPagination`选项设置为`true`
- 检查表格数据是否足够多以触发分页

### 3. 搜索功能不工作
- 确保`searchable`选项设置为`true`
- 检查表格数据是否正确提取

### 4. 排序功能不工作
- 确保`sortable`选项设置为`true`
- 点击表头进行排序

### 5. 选择功能不工作
- 确保`enableCheckbox`选项设置为`true`
- 检查是否正确引入了UIkit的checkbox样式

## 版本历史

### v1.0.0
- 初始版本，支持分页、搜索、排序、复选框选择、批量操作等功能
- 支持导入、导出、创建、编辑、删除等操作按钮
- 提供完整的文档和使用示例

## 开发说明

- 修改代码后无需重启服务器，PHP会自动解释执行
- 遵循项目的编码规范，保持代码风格一致
- 添加新功能时，确保在文档中更新相应的说明
- 优化性能时，注意避免不必要的DOM操作