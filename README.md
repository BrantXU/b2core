# B2Core 用户系统

一个简洁的用户管理系统，使用 PHP 开发。

## 功能特性

- 用户注册
  - 用户名验证
  - 密码设置及确认
  - 电子邮箱验证
- 用户登录
  - 账号密码验证
  - 登录状态维护

## 技术栈

- PHP
- Pure.css - 用于表单样式
- jQuery - 用于前端交互

## 目录结构

```
app/
├── v/          # 视图文件
│   └── user/   # 用户相关视图
├── m/          # 模型
└── c/          # 控制器
db/             # 数据库文件
```

## 开发规范

- 遵循 PSR-12 编码规范
- 使用 PDO 进行数据库操作
- 所有用户输入都经过 HTML 转义处理

## 安装使用

1. 克隆项目到本地
2. 配置 Web 服务器（Apache/Nginx）
3. 导入 db 目录下的数据库文件
4. 访问项目首页即可使用

## 使用教程

### 用户注册

1. 访问 `/user/register` 路径
2. 填写注册表单：
   - 用户名：必填，3-20个字符
   - 密码：必填，6位以上
   - 确认密码：必须与密码一致
   - 电子邮箱：必填，有效的邮箱格式

### 用户登录

1. 访问 `/user/login` 路径
2. 输入已注册的账号信息：
   - 用户名
   - 密码
3. 点击登录按钮

### 表单样式

本项目使用 Pure.css 框架，主要使用以下类：

```html
<!-- 表单基础类 -->
<form class="pure-form pure-form-stacked">

<!-- 按钮样式 -->
<button class="pure-button">普通按钮</button>
<button class="pure-button pure-button-primary">主要按钮</button>

<!-- 错误提示 -->
<div class="pure-alert pure-alert-error">错误信息</div>
```

## 安全特性

- 密码加密存储
- 防止 XSS 攻击（使用 htmlspecialchars）
- 表单验证
- 登录状态保护

## 开发教程

### 项目架构

本项目采用简化版的 MVC 架构：

```
app/
├── v/          # Views（视图层）
│   └── user/   # 用户相关视图
├── m/          # Models（模型层）
│   └── user.php # 用户相关数据处理
└── c/          # Controllers（控制器层）
    └── user.php # 用户相关业务逻辑
```

### 开发新功能的步骤

1. **创建数据表**
   - 在 `db` 目录下创建对应的 SQL 文件
   - 遵循表名使用小写加下划线的命名规范
   ```sql
   CREATE TABLE user (
     id INT PRIMARY KEY AUTO_INCREMENT,
     username VARCHAR(20) NOT NULL,
     ...
   );
   ```

2. **创建模型**
   - 在 `app/m` 目录下创建模型文件
   - 文件名使用小写，如：`user.php`
   ```php
   class UserModel {
     public function find($id) {
       // 数据库查询逻辑
     }
   }
   ```

3. **创建控制器**
   - 在 `app/c` 目录下创建控制器文件
   - 处理业务逻辑和数据验证
   ```php
   class UserController {
     public function login() {
       // 处理登录逻辑
     }
   }
   ```

4. **创建视图**
   - 在 `app/v` 目录下创建对应的视图文件
   - 使用 Pure.css 进行样式处理
   ```php
   <form class="pure-form pure-form-stacked">
     <!-- 表单内容 -->
   </form>
   ```

### 开发规范

1. **命名规范**
   - 类名：大驼峰命名（UserModel）
   - 方法名：小驼峰命名（getUserById）
   - 变量名：下划线命名（user_id）
   - 常量名：全大写下划线（MAX_LOGIN_ATTEMPTS）

2. **文件组织**
   - 每个功能模块对应一个目录
   - 相关的模型、视图、控制器放在对应目录
   - 公共函数放在 `helpers` 目录

3. **数据库操作**
   ```php
   // 使用 PDO 预处理语句
   $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
   $stmt->execute([$id]);
   ```

4. **错误处理**
   ```php
   try {
     // 数据库操作
   } catch (PDOException $e) {
     // 错误处理
   }
   ```

### 示例：添加新功能

以添加用户头像功能为例：

1. **更新数据表**
   ```sql
   ALTER TABLE user ADD COLUMN avatar VARCHAR(255);
   ```

2. **修改模型**
   ```php
   public function updateAvatar($user_id, $avatar_path) {
     $sql = "UPDATE user SET avatar = ? WHERE id = ?";
     return $this->db->execute($sql, [$avatar_path, $user_id]);
   }
   ```

3. **添加控制器方法**
   ```php
   public function uploadAvatar() {
     if ($_FILES['avatar']) {
       // 处理文件上传
       $path = $this->handleFileUpload($_FILES['avatar']);
       $this->user_model->updateAvatar($user_id, $path);
     }
   }
   ```

4. **创建视图**
   ```php
   <form class="pure-form" enctype="multipart/form-data">
     <input type="file" name="avatar" />
     <button class="pure-button pure-button-primary">
       上传头像
     </button>
   </form>
   ```

### 调试方法

1. **错误显示**
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

2. **日志记录**
   ```php
   error_log("Debug info: " . print_r($data, true));
   ```

3. **数据库调试**
   ```php
   $stmt->debugDumpParams();
   ```

### 安全建议

1. 所有用户输入必须验证和转义
2. 使用 PDO 预处理语句防止 SQL 注入
3. 文件上传需要验证类型和大小
4. 密码必须加密存储
5. 敏感操作需要验证用户权限

## 贡献指南

1. Fork 项目
2. 创建功能分支
3. 提交代码
4. 发起合并请求

## 许可证

MIT License

## 数据库配置

### PDO 支持

本框架默认使用 PDO 进行数据库操作，支持多种数据库：

- MySQL
- SQLite
- PostgreSQL
- 其他 PDO 支持的数据库

### 配置示例

```php
// config/database.php
return [
    'default' => [
        'driver'   => 'mysql',  // mysql, sqlite, pgsql 等
        'host'     => 'localhost',
        'database' => 'b2core',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
        'options'  => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
    // 可以配置多个数据库连接
    'sqlite' => [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/database.sqlite',
    ]
];
```

### PDO 使用示例

1. **基础模型类**
```php
// app/m/Model.php
class Model {
    protected $db;
    
    public function __construct() {
        $config = require 'config/database.php';
        $dsn = "{$config['default']['driver']}:host={$config['default']['host']};dbname={$config['default']['database']};charset={$config['default']['charset']}";
        
        try {
            $this->db = new PDO($dsn, 
                $config['default']['username'], 
                $config['default']['password'],
                $config['default']['options']
            );
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }

    protected function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
```

2. **用户模型示例**
```php
// app/m/user.php
class UserModel extends Model {
    public function findById($id) {
        $stmt = $this->query("SELECT * FROM users WHERE id = ?", [$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
        return $this->query($sql, [
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['email']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        return $this->query($sql, [$data['username'], $data['email'], $id]);
    }
}
```

3. **事务处理示例**
```php
try {
    $this->db->beginTransaction();
    
    // 执行多个数据库操作
    $this->query("INSERT INTO users ...");
    $this->query("UPDATE profiles ...");
    
    $this->db->commit();
} catch (Exception $e) {
    $this->db->rollBack();
    throw $e;
}
```

### PDO 查询技巧

1. **预处理语句**
```php
// 安全的查询方式
$stmt = $this->query("SELECT * FROM users WHERE username = ?", [$username]);
$user = $stmt->fetch();
```

2. **IN 查询**
```php
$ids = [1, 2, 3];
$placeholders = str_repeat('?,', count($ids) - 1) . '?';
$stmt = $this->query(
    "SELECT * FROM users WHERE id IN ($placeholders)",
    $ids
);
```

3. **分页查询**
```php
public function paginate($page = 1, $perPage = 10) {
    $offset = ($page - 1) * $perPage;
    return $this->query(
        "SELECT * FROM users LIMIT ? OFFSET ?",
        [$perPage, $offset]
    )->fetchAll();
}
```

### 调试技巧

1. **SQL 日志**
```php
$stmt = $this->db->prepare($sql);
$stmt->execute($params);
error_log("SQL: $sql, Params: " . print_r($params, true));
```

2. **错误处理**
```php
try {
    $stmt = $this->query($sql, $params);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    // 可以返回用户友好的错误信息
    throw new Exception("操作失败，请稍后重试");
}
```
