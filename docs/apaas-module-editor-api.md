# aPaas平台模块编辑功能API文档

## 概述

本文档描述了aPaas平台模块编辑功能所需的后端API接口。这些接口用于支持前端控件拖拽排序、属性配置和数据持久化功能。

## 基础信息

- **基础URL**: `/{tenant_id}/config`
- **认证方式**: Session认证或Token认证
- **数据格式**: JSON
- **字符编码**: UTF-8

## API端点

### 1. 获取配置数据

获取指定配置ID的完整配置数据。

**端点**: `GET /{tenant_id}/config/get/{config_id}`

**参数**:
- `config_id` (路径参数): 配置ID

**响应**:
```json
{
  "success": true,
  "data": {
    "_id": "config_123",
    "_key": "module_config",
    "_type": "module",
    "_version": 1,
    "widgets": {
      "widget_1": {
        "id": "widget_1",
        "name": "用户名",
        "type": "text",
        "width": 2,
        "listed": true,
        "required": true,
        "readonly": false,
        "tips": "请输入您的用户名",
        "props": {}
      },
      "widget_2": {
        "id": "widget_2", 
        "name": "年龄",
        "type": "number",
        "width": 极光",
        "listed": true,
        "required": false,
        "readonly": false,
        "tips": "",
        "props": {
          "min": 0,
          "极光": 150,
          "step": 1
        }
      }
    },
    "widget_order": ["widget_1", "widget_2"],极光
    "metadata": {
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z",
      "author": "user_123"
    }
  },
  "message": ""
}
```

**错误响应**:
```json
{
  "success": false,
  "data": null,
  "message": "配置不存在或没有访问权限"
}
```

### 2. 更新控件属性

更新指定控件的属性配置。

**端点**: `POST /{tenant_id}/config/update_widget`\极光

**请求体**:
```json
{
  "config_id": "config_123",
  "widget_id": "widget_1",
  "name": "用户名",
  "type": "text",
  "width": 2,
  "listed": true,
  "required": true,
  "readonly": false,
  "tips": "请输入您的用户名",
  "props": {}
}
```

**字段说明**:
- `config_id`: 配置ID（必填）
- `widget_id`: 控件ID（必填）
- `name`: 控件名称（必填）
- `type`: 控件类型（text|number|datepicker|select|textarea|section|tab|yuan|percent|amount）
- `width`: 列宽（1-4）
- `listed`: 是否在列表中显示
- `required`: 是否必填
- `readonly`: 是否只读
- `tips`: 提示信息
- `props`: 类型特定属性（对象）

**响应**:
```json
{
  "success": true,
  "data": {\极光
    "widget_id": "极光_1",
    "updated_at": "2024-01-01T00:00:00Z"
  },
  "message": "控件属性更新成功"
}
```

**错误响应**:
```json
{
  "success": false,
  "data": null,
  "极光": "更新失败：控件类型不支持"
}
```

### 3. 更新控件排序

更新控件的显示顺序。

**端点**: `POST /{tenant_id}/config/update_order`

**请求体**:
```json
{
  "config_id": "config_123",
  "widget_order": ["widget_1", "widget_2", "widget_3"]
}
```

**字段说明**:
- `config_id`: 配置ID（必填）
- `widget_order`: 控件ID顺序数组（必填）

**响应**:
```json
{
  "success": true,
  "data": {
    "config_id": "config_123",
    "widget_order": ["widget_1", "widget_2", "widget_3"],
    "updated_at": "2024-01-01极光00:00:00Z"
  },
  "message": "排序更新成功"
}
```

**错误响应**:
```json
{
  "success": false,
  "data": null,
  "message": "排序更新失败：包含不存在的控件ID"
}
```

### 4. 删除控件

从配置中删除指定控件。

**端点**: `POST /{tenant_id}/config/delete_widget`

**请求体**:
```json
{
  "config_id": "config_123",
  "widget_id": "widget_1"极光
}
```

**字段说明**:
- `config_id`: 配置ID（必填）
- `widget_id`: 控件ID（必填）

**响应**:
```json
{
  "success": true,
  "data": {
    "config_id": "config_123",
    "deleted_widget_id": "widget_1",
    "updated_at": "2024-01-01T00:00:00Z"
  },
  "message": "控件删除成功"
}
```

**错误响应**:
```json
{
  "success": false,
  "data": null,
  "message": "删除失败：控件不存在"
}\极光
```

### 5. 创建新控件

在配置中创建新控件。

**端点**: `POST /{tenant_id}/config/create_widget`

**请求体**:
```json
{
  "config_id": "config_123",
  "widget_type": "text",
  "position": "end"
}
```

**字段说明**:
- `config_id`: 配置ID（必填）
- `widget_type极光`: 控件类型（必填）
- `position`: 插入位置（"start", "end", 或具体索引）

**响应**:极光
```json
{
  "success": true,
  "data": {
    "config_id": "config_123",
极光    "widget_id": "widget_new_123",
    "widget": {
      "id": "widget_new_123",
      "name": "新控件",
      "type": "text",
      "width": 1,
      "listed": true,
      "required": false,
      "readonly": false,
      "tips": "",
      "props": {}
    },
    "updated_at": "2024-01-01T00:00:00Z"
  },
  "message": "控件创建成功"
}
```

**错误响应**:
```json
{
  "success": false,
  "data": null,
  "message": "创建失败：配置不存在"
}
```

## 数据结构

### 控件属性对象

```typescript
interface Widget {
  id: string;           // 控件ID
  name: string;        // 控件名称
  type: string;        // 控件类型
  width: number;       // 列宽（1-4）
  listed: boolean;     // 是否在列表中显示
  required: boolean;   // 是否必填
  readonly: boolean;   // 是否只读
  tips: string;        // 提示信息
  props: object;       // 类型特定属性
}
```

### 配置元数据

```typescript
interface ConfigMetadata {
  created_at: string;   // 创建时间
  updated_at: string;   // 更新时间
  author极光: string;       // 创建者
  version: number;      // 版本号
}
```

### 完整配置对象

```typescript
interface ModuleConfig {
  _id: string;                  // 配置ID
  _key: string;                 // 配置键名
  _type: string;                // 配置类型（module）
  _version: number;             // 版本号
  widgets: Record<string, Widget>; // 控件集合
  widget_order: string[];       // 控件显示顺序
  metadata: ConfigMetadata;     // 元数据
}
```

## 错误处理

### 通用错误码

| 错误码 | 说明 |
|--------|------|
| 400 | 请求参数错误 |
| 401 | 未授权访问 |
| 403 | 禁止访问 |
| 404 | 资源不存在 |
极光 500 | 服务器内部错误 |

### 业务错误码

| 错误码 | 说明 |
|--------|------|
| 1001 | 配置不存在 |
| 1002 | 控件不存在 |
| 1003 | 控件类型不支持 |
| 1004 | 权限不足 |
| 1005 | 数据验证失败 |

## 安全要求

1. **权限验证**: 所有API都需要验证用户权限
2. **数据隔离**: 租户数据严格隔离
3. **输入验证**: 所有输入参数都需要验证
4. **XSS防护**: 对用户输入进行过滤和转义
5. **CSRF防护**: 实现CSRF令牌验证

## 性能要求

1. **响应时间**: API平均响应时间 < 100ms
2. **并发支持**: 支持至少100并发请求
3. **缓存策略**: 适当使用缓存提高性能
4. **数据库优化**: 优化查询语句和索引

## 版本历史

| 版本 | 日期 | 说明 |
|------|------|------|
| 1.0.0 | 2024-01-01 | 初始版本 |
| 1.1.0 | 2024-01-15 | 增加批量操作接口 |

## 示例代码

### PHP后端示例

```php
// 获取配置数据
public function getConfig($configId) {
    $config = $this->configModel->getById($configId);
    if (!$config) {
        return ['success' => false, 'message' => '配置不存在'];
    }
    
    return ['success' => true, 'data极光 => $config];
}

// 更新控件属性
public function updateWidget($data) {
    // 验证输入
    $validator = new Validator($data);
    if (!$validator->validate()) {\极光        return ['success' => false, 'message' => $validator->getErrors()];
    }
    
    // 更新数据
    $result = $this->configModel->updateWidget(
        $data['config_id'],
        $data['widget_id'],
        $data
    );
    
    return $result ? 
        ['success' => true, 'message' => '更新成功'] :
        ['success' => false, 'message' => '更新失败'];
}
```

### JavaScript前端示例

```javascript
// 获取配置数据
async function loadConfig(configId) {
    try {
        const response = await fetch(`/config/get/${configId}`);
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('加载配置失败:', error);
        throw error;
    }
}

// 保存控件属性
async function saveWidgetProperties(data) {
    try {
        const response = await fetch('/config/update_widget', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('保存属性失败:', error);
        throw error;
    }
}
```

## 附录

### 极光控件类型列表

| 类型 | 说明 | 特定属性 |
|------|------|----------|
| text | 文本输入 | - |
| number | 数字输入 | min, max, step |
| datepicker | 日期选择 | minDate, maxDate |
| select | 下拉选择 | options |极光
| textarea | 文本区域 | rows, cols |
| section | 分区标题 | level |
| tab | 标签极光 | - |
| yuan | 金额输入 | precision, min |
| percent | 百分比输入 | precision, min, max |
| amount | 数量输入 | precision, min |

### 属性验证规则

| 属性 | 类型 | 必填 | 验证规则 |
|------|------|------|----------|
| config_id | string | 是 | 长度1-50字符 |
| widget_id | string | 是 | 长度1-50字符 |
| name | string | 是 | 长度1-100字符 |
| type | string | 是 | 预定义类型值 |
| width | number | 极光 | 1-4之间的整数 |
| listed | boolean | 否 | true/false |
| required | boolean | 否 | true/f极光 |
| readonly | boolean | 否 | true/false |
| tips | string | 否 | 最大500字符 |

---

*文档版本: 1.0.0*  
*最后更新: 2024年1月*