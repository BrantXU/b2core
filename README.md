# HumDB aPaaS 平台

一个简洁的应用平台即服务（aPaaS）平台，使用 PHP 开发，旨在提供快速应用开发和部署能力。

## 核心功能

### 多租户架构
- 租户管理（增删改查）
- 租户隔离的数据存储

### 用户管理系统
- 用户注册与身份验证
  - 用户名验证
  - 密码设置及确认
  - 电子邮箱验证
- 用户登录/登出
- 用户权限管理

### 配置管理中心
- 系统配置项管理
- 支持配置项的键值对存储
- 配置项描述信息

### 应用开发支持
- 响应式界面设计
- 统一的错误处理和表单验证
- 可扩展的模块化架构

## 技术架构

### 后端技术
- PHP - 核心编程语言
- SQLite/MySQL - 数据存储

### 前端技术
- Pure.css - 轻量级CSS框架
- jQuery - 前端交互处理

### 开发工具
- PSR-12 编码规范
- 原生PHP模板引擎

## 目录结构

```
app/
├── c/                  # 控制器
│   ├── __construct.php
│   ├── config.php
│   ├── home.php
│   ├── page.php
│   ├── tenant.php
│   └── user.php
├── config.php
├── db/                 # 数据库文件
│   ├── config.sql
│   ├── sqlite.sql
│   └── tenant.sql
├── db.sql
├── lib/                # 核心库文件
│   ├── b2core.php
│   ├── db.php
│   ├── m.php
│   └── utility.php
├── m/                  # 模型
│   ├── config_m.php
│   ├── tenant_m.php
│   └── user_m.php
└── v/                  # 视图文件
    ├── index.php
    ├── redirect.php
    ├── template.php
    ├── config/
    ├── tenant/
    └── user/
www/                    # Web 根目录
├── index.php
└── pure-min.css
```


### 定义路由

URI路由支持两种模式：

1. **标准路由模式**：uri 第一段为租户 ID，第二段为模块名称，第三段为方法名称，后面为参数
   例如：`http://domain/{tenant_id}/{module}/{method}`

2. **明确指定路由**：在`app/config.php`中配置的路由规则优先于标准路由模式
   例如：`/login/` -> `/user/login/`

### 菜单UI

1. 普通菜单，一级为模块二级为方法
2. 对象菜单，展示当前对象相关菜单。 例如在 数据 A 下 的对象菜单，有 A 的概况、 A 相关的文档、 A 相关的用户、 A 相关的权限等。



## 平台特性

- 轻量级架构设计
- 模块化开发模式
- 多数据库支持（SQLite/MySQL）
- 响应式Web界面
- 统一的错误处理机制
- 安全的用户身份验证

## 快速开始

1. 克隆项目到本地
2. 配置 Web 服务器（Apache/Nginx）或使用PHP内置服务器
3. 初始化数据库（支持SQLite和MySQL）
4. 访问管理后台开始构建您的应用

## 安全保障

- 密码加密存储（MD5）
- 防止 XSS 攻击（使用 htmlspecialchars）
- 表单验证与数据过滤
- 安全的用户会话管理
- 租户数据隔离
