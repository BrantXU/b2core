# B2Core 用户系统

一个简洁的用户管理系统，使用 PHP 开发。

## 功能特性

- 用户注册
  - 用户名验证
  - 密码设置及确认
  - 电子邮箱验证

## 技术栈

- PHP
- Pure.css - 用于表单样式
- jQuery - 用于前端交互
- Bootstrap - 用于UI组件（按钮样式等）

## 目录结构

```
app/
├── c/                  # 控制器
│   ├── __construct.php
│   ├── home.php
│   ├── page.php
│   └── user.php
├── config.php
├── db/                 # 数据库文件
│   └── sqlite.sql
├── db.sql
├── lib/                # 核心库文件
│   ├── b2core.php
│   ├── db.php
│   ├── m.php
│   └── utility.php
├── m/                  # 模型
│   └── user_m.php
└── v/                  # 视图文件
    ├── index.php
    ├── redirect.php
    ├── template.php
    └── user/
www/                    # Web 根目录
├── index.php
└── pure-min.css
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

## 安全特性

- 密码加密存储
- 防止 XSS 攻击（使用 htmlspecialchars）
- 表单验证
