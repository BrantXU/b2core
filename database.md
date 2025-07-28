# B2Core 数据库设计文档

## 概述
B2Core 支持两种数据库系统：SQLite 和 MySQL。默认使用 SQLite 作为开发和测试环境的数据库，MySQL 用于生产环境。

## 数据库配置
数据库配置在 `app/config.php` 文件中定义：

```php
// 数据库配置信息
$db_config = array(
    'driver' => 'sqlite',        // 数据库类型：mysql 或 sqlite
    'sqlite' => array(
        'database' => APP.'db.sqlite',  // SQLite数据库文件路径
    ),
    'mysql' => array(
        'host' => 'localhost',     // MySQL主机
        'user' => 'root',         // MySQL用户名
        'password' => 'root',     // MySQL密码
        'database' => 'b2core',   // MySQL数据库名
        'port' => 3306,          // MySQL端口号
        'charset' => 'utf8mb4'   // 字符集
    )
);
```

## 数据库表结构

### 用户表 (tb_user)

用户表用于存储系统用户信息。

```yaml
table_name: tb_user
description: 存储系统用户信息
fields:
  - name: id
    type: TEXT (SQLite) / INT (MySQL)
    constraint: 主键
    description: 用户唯一标识符
  - name: username
    type: TEXT / VARCHAR(45)
    constraint: 非空
    description: 用户名
  - name: password
    type: TEXT / VARCHAR(45)
    constraint: 非空
    description: 密码（MD5加密）
  - name: email
    type: TEXT / VARCHAR(45)
    constraint: 非空
    description: 邮箱地址
  - name: level
    type: INTEGER / INT
    constraint: 默认值0
    description: 用户级别（0:普通用户, 1:管理员）
```


### 实体表 (tb_entity)

实体表用于存储系统中的各种业务实体数据，支持多租户架构。

```yaml
table_name: tb_entity
description: 存储业务实体数据
fields:
  - name: id
    type: TEXT (SQLite) / VARCHAR(8)
    constraint: 主键
    description: 实体唯一标识符
  - name: tenant_id
    type: TEXT / VARCHAR(8)
    constraint: 非空
    description: 租户ID
  - name: name
    type: TEXT / VARCHAR(100)
    constraint: 非空
    description: 实体名称
  - name: type
    type: TEXT / VARCHAR(50)
    constraint: 非空
    description: 实体类型
  - name: data
    type: TEXT
    constraint: 可空
    description: 实体数据（JSON格式）
  - name: description
    type: TEXT / VARCHAR(255)
    constraint: 可空
    description: 实体描述
  - name: created_at
    type: DATETIME
    constraint: 默认当前时间戳
    description: 创建时间
  - name: updated_at
    type: DATETIME
    constraint: 默认当前时间戳
    description: 更新时间
```



### 配置表 (tb_config)

配置表用于存储系统配置信息。

```yaml
table_name: tb_config
description: 存储系统配置信息
fields:
  - name: id
    type: TEXT (SQLite) / VARCHAR(8)
    constraint: 主键
    description: 配置项唯一标识符
  - name: tenant_id
    type: TEXT / VARCHAR(8)
    constraint: 非空
    description: 租户ID
  - name: key
    type: TEXT / VARCHAR(100)
    constraint: 非空, 唯一
    description: 配置键名
  - name: value
    type: TEXT
    constraint: 非空
    description: 配置值
  - name: description
    type: TEXT / VARCHAR(255)
    constraint: 可空
    description: 配置描述
  - name: created_at
    type: DATETIME
    constraint: 默认当前时间戳
    description: 创建时间
  - name: updated_at
    type: DATETIME
    constraint: 默认当前时间戳
    description: 更新时间
```

### 用户租户关联表 (tb_user_tenant)

用户租户关联表用于存储用户和租户之间的多对多关系。

```yaml
table_name: tb_user_tenant
description: 存储用户和租户之间的关联关系
fields:
  - name: user_id
    type: TEXT (SQLite) / VARCHAR(8)
    constraint: 非空
    description: 用户ID
  - name: tenant_id
    type: TEXT / VARCHAR(8)
    constraint: 非空
    description: 租户ID
```

## 数据库特性

1. **ID 生成策略**：
   - SQLite 使用 TEXT 类型的主键，通过 `randstr()` 函数生成唯一ID
   - MySQL 使用自增的整数类型主键

2. **默认数据**：
   - 用户表包含默认管理员账户：admin / admin@b24.cn
   - 配置表包含默认网站名称配置：site_name = "B2Core系统" (tenant_id: default)

3. **时间戳**：
   - 配置表和实体表自动记录创建和更新时间

4. **数据库驱动**：
   - 通过 `db.php` 类实现统一的数据库操作接口
   - 支持 SQLite3 和 MySQLi 扩展

5. **实体表设计**：
   - 实体表采用灵活的 schema-less 设计，通过 `data` 字段存储 JSON 格式的实体数据
   - 支持多租户架构，通过 `tenant_id` 字段隔离不同租户的数据
   - 通过 `type` 字段区分不同类型的实体